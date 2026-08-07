<?php

namespace App\Console\Commands;

use App\Models\SalesOrder;
use App\Services\CommissionCalculator;
use Illuminate\Console\Command;

class CalculateMissingCommissions extends Command
{
    protected $signature = 'commissions:calculate-missing
                            {--limit=100 : Maximum number of orders to process}
                            {--chunk=200 : Number of orders to process per batch}
                            {--from-id= : Start processing from this sales_order ID}
                            {--to-id= : Stop processing at this sales_order ID}
                            {--force : Recalculate even if commission exists, including approved/rejected/paid}
                            {--update-unconfirmed : Also recalculate commissions still pending (status=pending)}
                            {--all : Recalculate everything without limit}';

    protected $description = 'Calculate commissions for sales orders that don\'t have them yet';

    protected CommissionCalculator $calculator;

    public function __construct(CommissionCalculator $calculator)
    {
        parent::__construct();
        $this->calculator = $calculator;
    }

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $chunkSize = max(1, (int) $this->option('chunk'));
        $force = $this->option('force');
        $updateUnconfirmed = $this->option('update-unconfirmed');
        $all = $this->option('all');
        $fromId = $this->option('from-id') !== null ? (int) $this->option('from-id') : null;
        $toId = $this->option('to-id') !== null ? (int) $this->option('to-id') : null;

        if ($all) {
            $limit = PHP_INT_MAX; // Effectively no limit
            $force = true;
        }

        $baseQuery = SalesOrder::query();

        if (! $force) {
            if ($updateUnconfirmed) {
                // status, not confirmed_by_manager: that's a separate sales-manager
                // signoff flag that can be true while status is still pending, or
                // false on an order nobody's touched yet - neither implies the
                // commission is safe to leave stale. status is the actual signal
                // for "has anyone signed off on the final numbers" (approved/
                // rejected/paid should never be silently recalculated).
                $baseQuery->where(function ($q) {
                    $q->whereDoesntHave('commissionCalculation')
                        ->orWhereHas('commissionCalculation', function ($sub) {
                            $sub->where('status', 'pending');
                        });
                });
            } else {
                $baseQuery->whereDoesntHave('commissionCalculation');
            }
        }

        if ($fromId !== null && $fromId > 0) {
            $baseQuery->where('id', '>=', $fromId);
        }

        if ($toId !== null && $toId > 0) {
            $baseQuery->where('id', '<=', $toId);
        }

        $totalToProcess = $all
            ? (clone $baseQuery)->count()
            : min($limit, (clone $baseQuery)->count());

        if ($totalToProcess === 0) {
            $this->info('No sales orders need commission calculation.');

            return Command::SUCCESS;
        }

        $this->info("Processing {$totalToProcess} sales orders in chunks of {$chunkSize}...");

        $bar = $this->output->createProgressBar($totalToProcess);
        $bar->start();

        $successful = 0;
        $failed = 0;
        $skipped = 0;
        $errors = [];

        $processed = 0;

        $baseQuery
            ->orderBy('id')
            ->chunkById($chunkSize, function ($orders) use (
                &$processed,
                $totalToProcess,
                &$successful,
                &$failed,
                &$skipped,
                &$errors,
                $bar
            ) {
                foreach ($orders as $order) {
                    if ($processed >= $totalToProcess) {
                        return false;
                    }

                    try {
                        $result = $this->calculator->calculateCommission($order);
                        if ($result === null) {
                            $skipped++;
                        } else {
                            $successful++;
                        }
                    } catch (\Exception $e) {
                        $failed++;
                        $errors[] = "Order #{$order->id}: {$e->getMessage()}";

                        // Log the error
                        \Log::warning("Commission calculation failed for order {$order->id}", [
                            'error' => $e->getMessage(),
                            'order_id' => $order->id,
                        ]);
                    }

                    $processed++;
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info("✓ Successfully calculated: {$successful}");

        if ($skipped > 0) {
            $this->warn("⊘ Skipped (no user): {$skipped}");
        }

        if ($failed > 0) {
            $this->warn("✗ Failed: {$failed}");

            if ($this->option('verbose')) {
                $this->newLine();
                $this->error('Errors:');
                foreach ($errors as $error) {
                    $this->line("  - {$error}");
                }
            }
        }

        return Command::SUCCESS;
    }
}
