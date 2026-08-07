<?php

namespace App\Console\Commands;

use App\Models\Contact;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use App\Models\SyncLog;
use App\Services\CommissionCalculator;
use App\Services\SyncLogger;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Obuchmann\OdooJsonRpc\Odoo;

class SyncOdooSalesOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odoo:sync-sales {--all : Force sync all records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Sales Orders from Odoo to local database (Golden Sync)';

    /**
     * Execute the console command.
     */
    public function handle(Odoo $odoo, SyncLogger $logger, CommissionCalculator $calculator)
    {
        $log = $logger->start($this->signature);
        $this->info('Starting Odoo Sales Order Sync (Golden Sync)...');

        // Build Odoo user_id → partner_id mapping from already-synced Contacts
        // instead of re-fetching all of res.users here. SyncContacts (which runs
        // earlier in the pipeline) already fetches res.users and persists the
        // same mapping as Contact.odoo_user_ids, so this avoids a full,
        // paginated res.users round-trip every single sync run.
        $this->info('Building Odoo User → Partner ID mapping from synced contacts...');
        $userToPartnerMap = [];
        try {
            Contact::query()
                ->whereNotNull('odoo_user_ids')
                ->pluck('odoo_user_ids', 'odoo_id')
                ->each(function ($userIdsJson, $partnerId) use (&$userToPartnerMap) {
                    foreach ((json_decode((string) $userIdsJson, true) ?: []) as $userId) {
                        $userToPartnerMap[(int) rtrim((string) $userId, '-G')] = $partnerId;
                    }
                });
            $this->info('Mapped '.count($userToPartnerMap).' Odoo users to partner IDs.');
        } catch (\Exception $e) {
            $this->warn('Could not build user→partner map: '.$e->getMessage());
            $this->warn('salesperson_partner_id will be null for this sync run.');
        }

        $specification = [
            'name' => (object) [],
            'create_date' => (object) [],
            'write_date' => (object) [],
            'partner_id' => (object) ['fields' => (object) ['display_name' => (object) []]],
            'partner_shipping_id' => (object) [
                'fields' => (object) [
                    'display_name' => (object) [],
                    'contact_address_complete' => (object) [],
                    'state_id' => (object) ['fields' => (object) ['display_name' => (object) []]],
                ],
            ],
            'user_id' => (object) ['fields' => (object) ['display_name' => (object) []]],
            'team_id' => (object) ['fields' => (object) ['display_name' => (object) []]],
            'amount_untaxed' => (object) [],
            'amount_tax' => (object) [],
            'amount_total' => (object) [],
            'amount_to_invoice' => (object) [],
            'delivery_status' => (object) [],
            'state' => (object) [],
            'tag_ids' => (object) [],
            'is_subscription' => (object) [],
            'subscription_state' => (object) [],
            'end_date' => (object) [],
            'x_studio_sales_rep_1' => (object) [],
            'x_studio_sales_source' => (object) [],
            'x_studio_commission_paid' => (object) [],
            'x_studio_referred_by' => (object) [],
            'x_studio_referrer_processed' => (object) [],
            'x_studio_payment_type' => (object) [],
            'installer' => (object) ['fields' => (object) ['id' => (object) [], 'display_name' => (object) []]],
            'x_studio_invoice_payment_status' => (object) [],
            // 'invoice_payment_status' => (object) [],
            'recurring_total' => (object) [],
            'plan_id' => (object) ['fields' => (object) ['display_name' => (object) []]],
            'start_date' => (object) [],
            'next_invoice_date' => (object) [],
            'order_line' => (object) [
                'fields' => (object) [
                    'id' => (object) [],
                    'name' => (object) [],
                    'product_uom_qty' => (object) [],
                    'price_unit' => (object) [],
                    'price_subtotal' => (object) [],
                    'price_total' => (object) [],
                    'qty_delivered' => (object) [],
                    'qty_invoiced' => (object) [],
                    'discount' => (object) [],
                    'tax_id' => (object) ['fields' => (object) ['display_name' => (object) []]],
                    'product_id' => (object) [
                        'fields' => (object) [
                            'id' => (object) [],
                            'display_name' => (object) [],
                            'default_code' => (object) [],
                            'categ_id' => (object) ['fields' => (object) ['display_name' => (object) []]],
                            'list_price' => (object) [],
                            'type' => (object) [],
                            'write_date' => (object) [],
                        ],
                    ],
                ],
            ],
        ];

        $limit = 500;
        $offset = 0;
        $totalSynced = 0;
        $syncedOdooIds = [];
        $domain = [
            ['tag_ids', 'in', [2]],
            ['state', '=', 'sale'],
        ];

        // Incremental Sync logic
        if (! $this->option('all')) {
            $lastSuccessfulSync = SyncLog::where('command', $this->signature)
                ->where('status', 'completed')
                ->latest('completed_at')
                ->first();

            if ($lastSuccessfulSync) {
                $lastSyncDate = $lastSuccessfulSync->completed_at->toDateTimeString();
                // Payment/invoice status changes on the linked invoice don't always
                // bump sale.order.write_date, so also catch orders whose invoices
                // were modified since the last sync (e.g. a payment being registered).
                $domain[] = '|';
                $domain[] = ['write_date', '>', $lastSyncDate];
                $domain[] = ['invoice_ids.write_date', '>', $lastSyncDate];
                $this->info("Fetching records modified since $lastSyncDate...");
            }
        }

        try {
            // Loop to fetch all pages
            do {
                $this->info("Fetching records offset $offset...");

                $response = $odoo->executeKw('sale.order', 'web_search_read', [
                    $domain,
                    $specification,
                    $offset,
                    $limit,
                    'write_date desc',
                ]);

                $orders = $response->records ?? (is_array($response) ? ($response['records'] ?? []) : []);

                if (empty($orders)) {
                    break;
                }

                $syncData = [];
                $orderLineSyncData = [];
                $odooIds = [];
                $odooProductMetadata = [];

                foreach ($orders as $order) {
                    $order = (object) $order;
                    $odooIds[] = $order->id;

                    $syncData[] = [
                        'odoo_id' => $order->id,
                        'name' => $order->name,
                        'create_date' => $order->create_date,
                        'write_date' => $order->write_date,

                        'partner_id' => $order->partner_id->id ?? null,
                        'partner_name' => $order->partner_id->display_name ?? null,
                        'partner_shipping_id' => $order->partner_shipping_id->id ?? null,
                        'partner_shipping_name' => $order->partner_shipping_id->display_name ?? null,
                        'partner_shipping_address' => $order->partner_shipping_id->contact_address_complete ?? null,
                        'partner_shipping_state' => $order->partner_shipping_id->state_id->display_name ?? null,

                        'user_id' => $order->user_id->id ?? null,
                        'user_name' => $order->user_id->display_name ?? null,
                        'salesperson_partner_id' => $userToPartnerMap[$order->user_id->id ?? 0] ?? null,

                        'team_id' => $order->team_id->id ?? null,
                        'team_name' => $order->team_id->display_name ?? null,
                        'installer_id' => $order->installer->id ?? null,
                        'installer_name' => $order->installer->display_name ?? null,

                        'x_studio_sales_rep_1' => is_array($order->x_studio_sales_rep_1) ? $order->x_studio_sales_rep_1[1] : ($order->x_studio_sales_rep_1 ?? null),
                        'x_studio_sales_source' => is_array($order->x_studio_sales_source) ? $order->x_studio_sales_source[1] : ($order->x_studio_sales_source ?? null),
                        'x_studio_commission_paid' => $order->x_studio_commission_paid ?? null,
                        'x_studio_referred_by' => is_array($order->x_studio_referred_by) ? $order->x_studio_referred_by[1] : ($order->x_studio_referred_by ?? null),
                        'x_studio_referrer_processed' => $order->x_studio_referrer_processed ?? null,
                        'x_studio_payment_type' => is_array($order->x_studio_payment_type) ? $order->x_studio_payment_type[1] : ($order->x_studio_payment_type ?? null),

                        'amount_untaxed' => $order->amount_untaxed ?? 0,
                        'amount_tax' => $order->amount_tax ?? 0,
                        'amount_total' => $order->amount_total,
                        'recurring_total' => $order->recurring_total ?? 0,
                        'plan_name' => $order->plan_id->display_name ?? null,
                        'subscription_plan_id' => $order->plan_id->id ?? null,
                        'amount_to_invoice' => $order->amount_to_invoice,

                        'delivery_status' => $order->delivery_status ?? null,
                        'x_studio_invoice_payment_status' => $this->resolvePaymentStatus($order),
                        'state' => $order->state,

                        'tag_ids' => json_encode($order->tag_ids ?? []),

                        'is_subscription' => $order->is_subscription ?? false,
                        'subscription_state' => $order->subscription_state ?? null,
                        'start_date' => (! empty($order->start_date) && ! str_starts_with($order->start_date, '1970')) ? $order->start_date : null,
                        'next_invoice_date' => (! empty($order->next_invoice_date) && ! str_starts_with($order->next_invoice_date, '1970')) ? $order->next_invoice_date : null,
                        'end_date' => (! empty($order->end_date) && ! str_starts_with($order->end_date, '1970')) ? $order->end_date : null,
                        'updated_at' => Carbon::now(),
                    ];

                    // Process Nested Order Lines
                    if (! empty($order->order_line)) {
                        foreach ($order->order_line as $line) {
                            $line = (object) $line;
                            $productId = $line->product_id->id ?? null;

                            // Collect Product Metadata for missing local products
                            if ($productId) {
                                $odooProductMetadata[$productId] = $line->product_id;
                            }

                            $orderLineSyncData[] = [
                                'odoo_id' => $line->id,
                                'odoo_order_id' => $order->id,
                                'odoo_product_id' => $productId,
                                'product_name' => $line->product_id->display_name ?? '',
                                'name' => $line->name,
                                'product_uom_qty' => $line->product_uom_qty,
                                'price_unit' => $line->price_unit,
                                'price_subtotal' => $line->price_subtotal,
                                'price_total' => $line->price_total,
                                'qty_delivered' => $line->qty_delivered ?? 0,
                                'qty_invoiced' => $line->qty_invoiced ?? 0,
                                'discount' => $line->discount ?? 0,
                                'tax_names' => ! empty($line->tax_id) && is_array($line->tax_id) ? implode(', ', array_map(function ($t) {
                                    return is_object($t) ? ($t->display_name ?? '') : (is_array($t) ? ($t['display_name'] ?? '') : '');
                                }, $line->tax_id)) : null,
                                'lower_name' => strtolower($line->product_id->display_name ?? ''),
                            ];
                        }
                    }
                }

                // Sync missing products from metadata
                if (! empty($odooProductMetadata)) {
                    $odooProductIds = array_keys($odooProductMetadata);
                    $existingLocalProductIds = Product::whereIn('odoo_id', $odooProductIds)->pluck('odoo_id')->toArray();
                    $missingProductIds = array_diff($odooProductIds, $existingLocalProductIds);

                    if (! empty($missingProductIds)) {
                        $this->info('Adding '.count($missingProductIds).' missing products from Golden Sync metadata...');
                        $missingProductData = [];
                        foreach ($missingProductIds as $mId) {
                            $mp = (object) $odooProductMetadata[$mId];
                            $missingProductData[] = [
                                'odoo_id' => $mp->id,
                                'name' => $mp->display_name,
                                'default_code' => $mp->default_code ?? null,
                                'categ_id' => $mp->categ_id->id ?? null,
                                'categ_name' => $mp->categ_id->display_name ?? null,
                                'list_price' => $mp->list_price ?? 0,
                                'type' => $mp->type ?? null,
                                'write_date' => $mp->write_date ?? null,
                            ];
                        }
                        Product::upsert($missingProductData, ['odoo_id'], [
                            'name', 'default_code', 'categ_id', 'categ_name', 'list_price', 'type', 'write_date',
                        ]);
                    }
                }

                // Batch Upsert Sales Orders
                $this->info('Upserting '.count($syncData).' sales orders...');
                SalesOrder::upsert($syncData, ['odoo_id'], [
                    'name',
                    'create_date',
                    'write_date',
                    'partner_id',
                    'partner_name',
                    'partner_shipping_id',
                    'partner_shipping_name',
                    'partner_shipping_address',
                    'partner_shipping_state',
                    'user_id',
                    'user_name',
                    'salesperson_partner_id',
                    'team_id',
                    'team_name',
                    'installer_id',
                    'installer_name',
                    'x_studio_sales_rep_1',
                    'x_studio_sales_source',
                    'x_studio_commission_paid',
                    'x_studio_referred_by',
                    'x_studio_referrer_processed',
                    'x_studio_payment_type',
                    'amount_untaxed',
                    'amount_tax',
                    'amount_total',
                    'recurring_total',
                    'plan_name',
                    'subscription_plan_id',
                    'amount_to_invoice',
                    'delivery_status',
                    'x_studio_invoice_payment_status',
                    'state',
                    'tag_ids',
                    'is_subscription',
                    'subscription_state',
                    'start_date',
                    'next_invoice_date',
                    'end_date',
                    'updated_at',
                ]);

                // Re-fetch to get local IDs for line mapping
                $orderMap = SalesOrder::whereIn('odoo_id', $odooIds)->get()->keyBy('odoo_id')->all();
                $productMap = Product::whereIn('odoo_id', array_keys($odooProductMetadata))->pluck('id', 'odoo_id')->toArray();

                // Prepare final Line data with local IDs
                $finalLineData = [];
                foreach ($orderLineSyncData as $ld) {
                    $localOrder = $orderMap[$ld['odoo_order_id']] ?? null;
                    if ($localOrder) {
                        $finalLineData[] = [
                            'odoo_id' => $ld['odoo_id'],
                            'sales_order_id' => $localOrder->id,
                            'odoo_order_id' => $ld['odoo_order_id'],
                            'product_id' => $ld['odoo_product_id'] ? ($productMap[$ld['odoo_product_id']] ?? null) : null,
                            'product_name' => $ld['product_name'],
                            'name' => $ld['name'],
                            'product_uom_qty' => $ld['product_uom_qty'],
                            'price_unit' => $ld['price_unit'],
                            'price_subtotal' => $ld['price_subtotal'],
                            'price_total' => $ld['price_total'],
                            'qty_delivered' => $ld['qty_delivered'],
                            'qty_invoiced' => $ld['qty_invoiced'],
                            'discount' => $ld['discount'],
                            'tax_names' => $ld['tax_names'],
                            'is_supply_only' => str_contains($ld['lower_name'], 'supply only'),
                            'is_installation_service' => str_contains($ld['lower_name'], 'installation service'),
                            'updated_at' => Carbon::now(),
                        ];
                    }
                }

                if (! empty($finalLineData)) {
                    $this->info('Upserting '.count($finalLineData).' order lines...');
                    SalesOrderLine::upsert($finalLineData, ['odoo_id'], [
                        'sales_order_id',
                        'odoo_order_id',
                        'product_id',
                        'product_name',
                        'name',
                        'product_uom_qty',
                        'price_unit',
                        'price_subtotal',
                        'price_total',
                        'qty_delivered',
                        'qty_invoiced',
                        'discount',
                        'tax_names',
                        'is_supply_only',
                        'is_installation_service',
                        'updated_at',
                    ]);

                    // Refresh denormalized data for affected contacts
                    $affectedSalesOrderIds = array_unique(array_column($finalLineData, 'sales_order_id'));
                    $partnerOdooIds = SalesOrder::whereIn('id', $affectedSalesOrderIds)->pluck('partner_id')->unique()->toArray();
                    Contact::whereIn('odoo_id', $partnerOdooIds)->get()->each->refreshDenormalizedData();
                }

                $totalSynced += count($orders);
                array_push($syncedOdooIds, ...$odooIds);
                $offset += $limit;

            } while (count($orders) === $limit);

            $this->info("Sync complete. Total records: $totalSynced");

            // Auto-calculate commissions for orders synced this run - only those with
            // no commission yet, or still pending (status is the source of truth here,
            // not confirmed_by_manager, which is a separate manager-signoff step and
            // can be true while status is still pending). Approved/rejected/paid
            // commissions are left untouched: their numbers may have already been
            // reviewed/paid out against the prior calculation, so they shouldn't
            // silently change on the next sync.
            $this->recalculateCommissionsForSyncedOrders($calculator, array_unique($syncedOdooIds));

            $logger->complete($log, $totalSynced, (isset($domain) && ! empty($domain)) ? 'Incremental sync completed.' : 'Full sync completed.');
            Cache::forget('contacts_dashboard_stats');

            return 0;

        } catch (\Exception $e) {
            $this->error('Failed to fetch from Odoo: '.$e->getMessage());
            Log::error('Odoo Sync Error: '.$e->getMessage());
            $logger->fail($log, $e);

            return 1;
        }
    }

    /**
     * Recalculate commissions for exactly the orders synced this run, skipping
     * any whose commission is already approved/rejected/paid, or that a human
     * has already manually adjusted (even while still pending - the adjustment
     * dollar amount is preserved across a recalculation, but the total can
     * still shift underneath it if base/extra_commission change, which could
     * surprise whoever added that adjustment). Failures are logged per-order
     * rather than aborting the sync.
     *
     * @param  array<int>  $syncedOdooIds
     */
    private function recalculateCommissionsForSyncedOrders(CommissionCalculator $calculator, array $syncedOdooIds): void
    {
        if (empty($syncedOdooIds)) {
            return;
        }

        $orders = SalesOrder::whereIn('odoo_id', $syncedOdooIds)
            ->where(function ($query) {
                $query->whereDoesntHave('commissionCalculation')
                    ->orWhereHas('commissionCalculation', function ($sub) {
                        $sub->where('status', 'pending')
                            ->where('manual_adjustment', 0);
                    });
            })
            ->get();

        if ($orders->isEmpty()) {
            return;
        }

        $this->info("Calculating commissions for {$orders->count()} synced order(s)...");

        foreach ($orders as $order) {
            try {
                $calculator->calculateCommission($order);
            } catch (\Exception $e) {
                Log::warning("Commission calculation failed for order {$order->id}", [
                    'error' => $e->getMessage(),
                    'order_id' => $order->id,
                ]);
            }
        }
    }

    /**
     * Resolve the effective invoice payment status for a sale.order record.
     *
     * Odoo returns boolean false for custom studio fields that have never been
     * explicitly set. In that case we fall back to checking amount_to_invoice:
     * a fully invoiced/delivered order with nothing left to invoice is paid.
     *
     * Priority:
     *   1. x_studio_invoice_payment_status (custom) — if it's a real string value
     *   2. amount_to_invoice == 0                     — nothing owing, treat as paid
     *   3. null                                        — truly unknown
     */
    private function resolvePaymentStatus(object $order): ?string
    {
        $custom = $order->x_studio_invoice_payment_status ?? null;

        // Odoo returns boolean false for unset custom fields.
        // Treat false / empty string / literal "false" as "not set".
        if ($custom !== null && $custom !== false && $custom !== '' && $custom !== 'false') {
            return (string) $custom;
        }

        if (($order->amount_to_invoice ?? null) !== null && (float) $order->amount_to_invoice === 0.0) {
            return 'paid';
        }

        return null;
    }
}
