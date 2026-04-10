<?php

namespace App\Console\Commands;

use App\Models\ProductStock;
use App\Models\Warehouse;
use App\Models\SyncLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Obuchmann\OdooJsonRpc\Odoo;
use App\Services\SyncLogger;
use Carbon\Carbon;

class SyncStocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odoo:sync-stocks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Warehouses and Product Stocks from Odoo (Optimized)';

    /**
     * Execute the console command.
     */
    public function handle(Odoo $odoo, SyncLogger $logger)
    {
        $log = $logger->start($this->signature);
        $this->info('Starting Odoo Warehouse and Stock Sync (Optimized)...');
        $totalSynced = 0;

        try {
            // Step 1: Sync Global Stocks (All Warehouses)
            $this->info('Syncing Global Stocks (All Warehouses)...');
            $allWarehouse = Warehouse::updateOrCreate(
                ['odoo_id' => 0],
                [
                    'code' => 'ALL',
                    'name' => 'All Warehouses',
                ]
            );
            $totalSynced += $this->syncProductStocks($odoo, $allWarehouse);

            // Step 2: Sync Warehouses
            $this->info('Fetching individual warehouses...');
            $warehouses = $this->syncWarehouses($odoo);

            // Step 3: Sync Product Stocks for each warehouse
            foreach ($warehouses as $warehouse) {
                if ($warehouse->odoo_id === 0) continue;
                
                $this->info("Syncing stocks for warehouse: {$warehouse->name} (ID: {$warehouse->odoo_id})");
                $totalSynced += $this->syncProductStocks($odoo, $warehouse);
            }

            $this->info('Sync complete!');
            $logger->complete($log, $totalSynced);
            return 0;
        } catch (\Exception $e) {
            $this->error("Sync failed: " . $e->getMessage());
            $logger->fail($log, $e);
            return 1;
        }
    }

    protected function syncWarehouses(Odoo $odoo): array
    {
        try {
            $warehouseData = $odoo->model('stock.warehouse')
                ->fields(['id', 'name', 'code'])
                ->get();

            $warehouses = [];
            foreach ($warehouseData as $data) {
                $warehouse = Warehouse::updateOrCreate(
                    ['odoo_id' => $data->id],
                    [
                        'code' => $data->code ?? null,
                        'name' => $data->name,
                    ]
                );
                $warehouses[] = $warehouse;
            }

            $this->info('Synced ' . count($warehouses) . ' warehouses');
            return $warehouses;
        } catch (\Exception $e) {
            $this->error("Failed to fetch warehouses: " . $e->getMessage());
            Log::error("Odoo Warehouse Sync Error: " . $e->getMessage());
            return [];
        }
    }

    protected function syncProductStocks(Odoo $odoo, Warehouse $warehouse)
    {
        try {
            $fields = [
                'id',
                'display_name',
                'categ_id',
                'cost_method',
                'standard_price',
                'qty_available',
                'free_qty',
                'incoming_qty',
                'outgoing_qty',
                'virtual_available',
            ];

            $syncedOdooIds = [];
            $offset = 0;
            $limit = 1000;

            // Prepare specification for web_search_read
            $specification = [];
            foreach ($fields as $field) {
                $specification[$field] = (object)[];
            }

            while (true) {
                // If this is a specific warehouse (not "All Warehouses"), set the context
                $originalContext = $odoo->getContext();
                if ($warehouse->odoo_id > 0) {
                    $newContext = clone $originalContext;
                    $newContext->setContextArg('warehouse', $warehouse->odoo_id);
                    $odoo->setContext($newContext);
                }

                $this->info(" Fetching batch (offset: $offset)...");
                $response = $odoo->executeKw('product.product', 'web_search_read', [
                    [['is_storable', '=', true]],
                    $specification,
                    $offset,
                    $limit,
                    'id desc'
                ]);
                
                // Restore original context
                $odoo->setContext($originalContext);

                $products = $response->records ?? (is_array($response) ? ($response['records'] ?? []) : []);

                if (empty($products)) {
                    break;
                }

                $syncData = [];
                foreach ($products as $product) {
                    $product = (object)$product;
                    $syncedOdooIds[] = $product->id;

                    $categId = $product->categ_id->id ?? null;
                    $categName = $product->categ_id->display_name ?? null;

                    // Calculate total value
                    $totalValue = ($product->standard_price ?? 0) * ($product->qty_available ?? 0);

                    $syncData[] = [
                        'odoo_id' => $product->id,
                        'warehouse_id' => $warehouse->id,
                        'display_name' => $product->display_name ?? '',
                        'categ_id' => $categId,
                        'categ_name' => $categName,
                        'cost_method' => $product->cost_method ?? null,
                        'avg_cost' => $product->standard_price ?? 0,
                        'total_value' => $totalValue,
                        'qty_available' => $product->qty_available ?? 0,
                        'free_qty' => $product->free_qty ?? 0,
                        'incoming_qty' => $product->incoming_qty ?? 0,
                        'outgoing_qty' => $product->outgoing_qty ?? 0,
                        'virtual_available' => $product->virtual_available ?? 0,
                        'updated_at' => Carbon::now(),
                    ];
                }

                $this->info(" Upserting " . count($syncData) . " product stocks...");
                ProductStock::upsert($syncData, ['odoo_id', 'warehouse_id'], [
                    'display_name', 'categ_id', 'categ_name', 'cost_method', 'avg_cost', 
                    'total_value', 'qty_available', 'free_qty', 'incoming_qty', 
                    'outgoing_qty', 'virtual_available', 'updated_at'
                ]);

                if (count($products) < $limit) {
                    break;
                }
                $offset += $limit;
            }

            // Delete local stocks for this warehouse that were NOT in the synced list (non-storable or deleted in Odoo)
            $deletedCount = ProductStock::where('warehouse_id', $warehouse->id)
                ->whereNotIn('odoo_id', $syncedOdooIds)
                ->delete();

            if ($deletedCount > 0) {
                $this->warn(" Deleted $deletedCount obsolete stock records for warehouse: {$warehouse->name}");
            }

            $this->info(" Synced " . count($syncedOdooIds) . " products for warehouse " . $warehouse->name);
            return count($syncedOdooIds);
        } catch (\Exception $e) {
            $this->error("Failed to fetch stocks for warehouse {$warehouse->name}: " . $e->getMessage());
            Log::error("Odoo Stock Sync Error for warehouse {$warehouse->odoo_id}: " . $e->getMessage());
            return 0;
        }
    }
}
