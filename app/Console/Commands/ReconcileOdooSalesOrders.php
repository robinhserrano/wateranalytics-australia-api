<?php

namespace App\Console\Commands;

use App\Models\CommissionCalculation;
use App\Models\SalesOrder;
use App\Services\OdooClient;
use App\Services\SyncLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * The incremental sales sync only ever fetches orders inside SyncOdooSalesOrders::SCOPE_DOMAIN, so an
 * order that later leaves it (cancelled, tag removed, deleted) stays frozen locally as `sale` - still on
 * every page, dashboard and report (they all filter state = 'sale'). This pass finds those orders and
 * copies their real Odoo state/tags across, or flags them odoo_missing_at when Odoo no longer has them.
 * Read-only towards Odoo.
 */
class ReconcileOdooSalesOrders extends Command
{
    protected $signature = 'odoo:reconcile-sales';

    protected $description = 'Correct local sales orders that have left the Odoo sync scope (cancelled, untagged, deleted)';

    // ponytail: refuse to flag more than this share of local orders as deleted in one run - that many
    // vanishing at once means an Odoo access/connection problem, not real deletions
    private const MAX_MISSING_SHARE = 0.2;

    public function handle(OdooClient $odoo, SyncLogger $logger): int
    {
        $log = $logger->start($this->getName());

        try {
            $inScope = collect($odoo->search('sale.order', SyncOdooSalesOrders::SCOPE_DOMAIN));

            $stale = SalesOrder::where('state', 'sale')->whereNull('odoo_missing_at')
                ->pluck('odoo_id')->diff($inScope)->values();

            $current = $stale->chunk(500)->flatMap(fn ($ids) => $odoo->searchRead('sale.order', [['id', 'in', $ids->values()->all()]], ['state', 'tag_ids']))
                ->keyBy(fn ($order) => data_get($order, 'id'));
            $missing = $stale->diff($current->keys())->values();

            $localTotal = SalesOrder::count();
            if ($localTotal > 0 && $missing->count() / $localTotal > self::MAX_MISSING_SHARE) {
                throw new RuntimeException("Refusing: {$missing->count()} of {$localTotal} orders would be flagged as deleted in Odoo - check Odoo access before reconciling.");
            }

            foreach ($current as $odooId => $order) {
                SalesOrder::where('odoo_id', $odooId)->update([
                    'state' => data_get($order, 'state'),
                    'tag_ids' => json_encode(array_values((array) data_get($order, 'tag_ids', []))),
                ]);
            }
            SalesOrder::whereIn('odoo_id', $missing)->update(['odoo_missing_at' => now()]);

            // Pending commissions on cancelled/deleted orders would otherwise be approved and paid. Covers
            // every such order, not just tonight's, so earlier ones are caught too. Untagged or reverted-to-
            // quotation orders may come back, so they're left alone; manually adjusted ones (a person has
            // worked on them) are only reported. The sales sync undoes this if an order is reinstated.
            $onDeadOrders = CommissionCalculation::where('status', 'pending')
                ->whereHas('salesOrder', fn ($q) => $q->where('state', 'cancel')->orWhereNotNull('odoo_missing_at'));
            $adjustedLeft = (clone $onDeadOrders)->where('manual_adjustment', '!=', 0)->count();
            $autoRejected = (clone $onDeadOrders)->where('manual_adjustment', 0)->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejection_reason' => CommissionCalculation::AUTO_REJECT_REASON,
            ]);

            $byState = $current->countBy(fn ($o) => data_get($o, 'state').(in_array(2, (array) data_get($o, 'tag_ids', [])) ? '' : ' (untagged)'));
            $neverSynced = $inScope->diff(SalesOrder::pluck('odoo_id'))->count();
            $message = "Checked {$stale->count()} out-of-scope order(s): updated {$current->count()} "
                .($byState->isEmpty() ? '' : '['.$byState->map(fn ($n, $s) => "{$s}: {$n}")->implode(', ').'] ')
                ."flagged {$missing->count()} deleted; {$neverSynced} in-scope order(s) not stored locally; "
                ."auto-rejected {$autoRejected} pending commission(s) on cancelled/deleted orders"
                .($adjustedLeft ? ", {$adjustedLeft} manually adjusted one(s) left for review" : '').'.';

            if ($stale->isNotEmpty() || $neverSynced > 0 || $autoRejected > 0 || $adjustedLeft > 0) {
                Log::warning("odoo:reconcile-sales: {$message}", ['deleted_odoo_ids' => $missing->take(50)->all()]);
            }
        } catch (Throwable $e) {
            $logger->fail($log, $e);
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $logger->complete($log, $current->count() + $missing->count(), $message);
        $this->info($message);

        return self::SUCCESS;
    }
}
