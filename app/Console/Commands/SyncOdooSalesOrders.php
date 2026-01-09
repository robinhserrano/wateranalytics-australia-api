<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Obuchmann\OdooJsonRpc\Odoo;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\Log;

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
    protected $description = 'Sync Sales Orders from Odoo to local database';

    /**
     * Execute the console command.
     */
    public function handle(Odoo $odoo)
    {
        $this->info('Starting Odoo Sales Order Sync...');

        $fields = [
            'name',
            'create_date',
            'partner_id',
            'x_studio_sales_rep_1',
            'x_studio_sales_source',
            'x_studio_commission_paid',
            'x_studio_referred_by',
            'x_studio_referrer_processed',
            'x_studio_payment_type',
            'amount_total',
            'delivery_status',
            'amount_to_invoice',
            'x_studio_invoice_payment_status',
            // 'internal_note_display',
            'state',
            'user_id',
            'team_id',
            'tag_ids',
            'order_line',
            'amount_untaxed',
        ];

        $limit = 500;
        $offset = 0;
        $totalSynced = 0;

        // Loop to fetch all pages
        do {
            $this->info("Fetching records offset $offset...");
            
            try {
                $orders = $odoo->model('sale.order')
                    ->fields($fields)
                    ->where('tag_ids', 'in', [2]) // Filter as per controller usage
                    ->limit($limit)
                    ->offset($offset)
                    ->orderBy('id', 'desc')
                    ->get();
            } catch (\Exception $e) {
                $this->error("Failed to fetch from Odoo: " . $e->getMessage());
                Log::error("Odoo Sync Error: " . $e->getMessage());
                return 1;
            }

            if (empty($orders)) {
                break;
            }

            $bar = $this->output->createProgressBar(count($orders));
            $bar->start();

            $allOrderLineIds = [];
            $orderMap = []; // Map Odoo ID to Local Model

            foreach ($orders as $order) {
                // Map Odoo data to local model
                $data = [
                    'odoo_id' => $order->id,
                    'name' => $order->name,
                    'create_date' => $order->create_date,
                    
                    'partner_id' => is_array($order->partner_id) ? $order->partner_id[0] : null,
                    'partner_name' => is_array($order->partner_id) ? $order->partner_id[1] : null,
                    
                    'user_id' => is_array($order->user_id) ? $order->user_id[0] : null,
                    'user_name' => is_array($order->user_id) ? $order->user_id[1] : null,
                    
                    'team_id' => is_array($order->team_id) ? $order->team_id[0] : null,
                    'team_name' => is_array($order->team_id) ? $order->team_id[1] : null,

                    // Custom Fields
                    'x_studio_sales_rep_1' => is_array($order->x_studio_sales_rep_1) ? $order->x_studio_sales_rep_1[1] : ($order->x_studio_sales_rep_1 ?? null),
                    'x_studio_sales_source' => is_array($order->x_studio_sales_source) ? $order->x_studio_sales_source[1] : ($order->x_studio_sales_source ?? null),
                    'x_studio_commission_paid' => $order->x_studio_commission_paid ?? null,
                    'x_studio_referred_by' => is_array($order->x_studio_referred_by) ? $order->x_studio_referred_by[1] : ($order->x_studio_referred_by ?? null),
                    'x_studio_referrer_processed' => $order->x_studio_referrer_processed ?? null,
                    'x_studio_payment_type' => is_array($order->x_studio_payment_type) ? $order->x_studio_payment_type[1] : ($order->x_studio_payment_type ?? null),
                    
                    'amount_total' => $order->amount_total,
                    'amount_to_invoice' => $order->amount_to_invoice,
                    
                    'delivery_status' => $order->delivery_status ?? null,
                    'x_studio_invoice_payment_status' => $order->x_studio_invoice_payment_status ?? null,
                    'state' => $order->state,
                    
                    // 'internal_note_display' => $order->internal_note_display ?? null,
                    
                    'tag_ids' => $order->tag_ids,
                    'order_line' => $order->order_line, // Keep array for reference
                    // 'base_amount' => $order->amount_untaxed,
                ];

                try {
                    $localOrder = SalesOrder::updateOrCreate(
                        ['odoo_id' => $order->id],
                        $data
                    );
                } catch (\Illuminate\Database\QueryException $e) {
                     $this->error("Query Exception Details:");
                     $this->error("SQL: " . $e->getSql());
                     $this->error("Bindings count: " . count($e->getBindings()));
                     foreach ($e->getBindings() as $i => $binding) {
                          $this->info("Binding $i: " . (is_array($binding) ? json_encode($binding) : $binding));
                     }
                     throw $e;
                }
                
                $orderMap[$order->id] = $localOrder;

                // Collect line IDs
                if (!empty($order->order_line)) {
                    foreach ($order->order_line as $lineId) {
                        $allOrderLineIds[] = $lineId;
                    }
                }
                
                $totalSynced++;
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();

            // Sync Lines
            if (!empty($allOrderLineIds)) {
                $this->info("Syncing " . count($allOrderLineIds) . " order lines...");
                $this->syncOrderLines($odoo, $allOrderLineIds, $orderMap);
            }

            $offset += $limit;

        } while (count($orders) === $limit);

        $this->info("Sync complete. Total records: $totalSynced");
        
        // Auto-calculate commissions for newly synced orders
        $this->info('Calculating commissions for sales orders...');
        $this->call('commissions:calculate-missing', ['--limit' => $totalSynced]);
        
        return 0;
    }

    protected function syncOrderLines(Odoo $odoo, array $lineIds, array $orderMap)
    {
        $chunkSize = 500; // Fetch lines in chunks to avoid huge requests
        $chunks = array_chunk($lineIds, $chunkSize);

        foreach ($chunks as $chunk) {
            try {
                $lines = $odoo->model('sale.order.line')
                    ->where('id', 'in', $chunk)
                    ->fields(['order_id', 'product_id', 'name', 'product_uom_qty', 'price_unit', 'price_subtotal', 'price_total'])
                    ->get();

                // Prefetch/Create Products
                $odooProductData = []; 
                foreach ($lines as $line) {
                    if (!empty($line->product_id) && is_array($line->product_id)) {
                        $odooProductData[$line->product_id[0]] = $line->product_id[1];
                    }
                }

                $localProductMap = [];
                if (!empty($odooProductData)) {
                    $odooIds = array_keys($odooProductData);
                    $localProductMap = \App\Models\Product::whereIn('odoo_id', $odooIds)->pluck('id', 'odoo_id')->toArray();

                    $missingIds = array_diff($odooIds, array_keys($localProductMap));
                    foreach ($missingIds as $missingId) {
                        try {
                            $newProduct = \App\Models\Product::create([
                                'odoo_id' => $missingId,
                                'name' => $odooProductData[$missingId],
                            ]);
                            $localProductMap[$missingId] = $newProduct->id;
                        } catch (\Exception $e) {
                            Log::error("Failed to create missing product {$missingId}: " . $e->getMessage());
                        }
                    }
                }

                foreach ($lines as $line) {
                    $odooOrderId = is_array($line->order_id) ? $line->order_id[0] : $line->order_id;
                    
                    // Find local sales order
                    // We might have it in $orderMap, or we might need to query if it was from a previous batch (unlikely given logic but safe to check)
                    $localOrder = $orderMap[$odooOrderId] ?? SalesOrder::where('odoo_id', $odooOrderId)->first();

                    if ($localOrder) {
                        $odooProductId = is_array($line->product_id) ? $line->product_id[0] : null;
                        $localProductId = $odooProductId ? ($localProductMap[$odooProductId] ?? null) : null;

                        $productName = is_array($line->product_id) ? $line->product_id[1] : ($line->product_name ?? '');
                        $lowerName = strtolower($productName);

                        \App\Models\SalesOrderLine::updateOrCreate(
                            ['odoo_id' => $line->id],
                            [
                                'sales_order_id' => $localOrder->id,
                                'odoo_order_id' => $odooOrderId,
                                'product_id' => $localProductId,
                                'product_name' => $productName,
                                'name' => $line->name,
                                'product_uom_qty' => $line->product_uom_qty,
                                'price_unit' => $line->price_unit,
                                'price_subtotal' => $line->price_subtotal,
                                'price_total' => $line->price_total,
                                'is_supply_only' => str_contains($lowerName, 'supply only'),
                                'is_installation_service' => str_contains($lowerName, 'installation service'),
                            ]
                        );
                    }
                }
            } catch (\Exception $e) {
                $this->error("Failed to fetch lines chunk: " . $e->getMessage());
                Log::error("Odoo Line Sync Error: " . $e->getMessage());
            }
        }
    }
}
