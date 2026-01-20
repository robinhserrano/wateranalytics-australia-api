<?php

namespace App\Console\Commands;

use App\Models\ProductStock;
use App\Models\Warehouse;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Obuchmann\OdooJsonRpc\Odoo;
use App\Services\SyncLogger;

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
    protected $description = 'Sync Warehouses and Product Stocks from Odoo';

    /**
     * Execute the console command.
     */
    public function handle(Odoo $odoo, SyncLogger $logger)
    {
        $log = $logger->start('odoo:sync-stocks');
        $this->info('Starting Odoo Warehouse and Stock Sync...');
        $totalSynced = 0;

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
    }

    protected function syncWarehouses(Odoo $odoo): array
    {
        try {
            // Fetch warehouses using standard search/read pattern
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
            $hasMore = true;

            while ($hasMore) {
                // If this is a specific warehouse (not "All Warehouses"), set the context
                $originalContext = $odoo->getContext();
                if ($warehouse->odoo_id > 0) {
                    $newContext = clone $originalContext;
                    $newContext->setContextArg('warehouse', $warehouse->odoo_id);
                    $odoo->setContext($newContext);
                }

                $products = $odoo->model('product.product')
                    ->where('is_storable', '=', true)
                    ->fields($fields)
                    ->offset($offset)
                    ->limit($limit)
                    ->get();
                
                // Restore original context
                $odoo->setContext($originalContext);

                if (empty($products)) {
                    $hasMore = false;
                    continue;
                }

                $this->info(" Processing batch of " . count($products) . " products (offset: $offset)...");
                $bar = $this->output->createProgressBar(count($products));
                $bar->start();

                foreach ($products as $product) {
                    $syncedOdooIds[] = $product->id;
                    $categId = null;
                    $categName = null;

                    if (!empty($product->categ_id) && is_array($product->categ_id)) {
                        $categId = $product->categ_id[0];
                        $categName = $product->categ_id[1];
                    }

                    // Calculate total value
                    $totalValue = ($product->standard_price ?? 0) * ($product->qty_available ?? 0);

                    ProductStock::updateOrCreate(
                        [
                            'odoo_id' => $product->id,
                            'warehouse_id' => $warehouse->id,
                        ],
                        [
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
                        ]
                    );

                    $bar->advance();
                }

                $bar->finish();
                $this->newLine();

                if (count($products) < $limit) {
                    $hasMore = false;
                } else {
                    $offset += $limit;
                }
            }

            // Delete local stocks for this warehouse that were NOT in the synced list (non-storable or deleted in Odoo)
            $deletedCount = ProductStock::where('warehouse_id', $warehouse->id)
                ->whereNotIn('odoo_id', $syncedOdooIds)
                ->delete();

            if ($deletedCount > 0) {
                $this->warn("Deleted $deletedCount non-storable or obsolete stock records for warehouse: {$warehouse->name}");
            }

            $this->info('Synced ' . count($syncedOdooIds) . ' products for warehouse ' . $warehouse->name);
            return count($syncedOdooIds);
        } catch (\Exception $e) {
            $this->error("Failed to fetch stocks for warehouse {$warehouse->name}: " . $e->getMessage());
            Log::error("Odoo Stock Sync Error for warehouse {$warehouse->odoo_id}: " . $e->getMessage());
            return 0;
        }
    }
}
