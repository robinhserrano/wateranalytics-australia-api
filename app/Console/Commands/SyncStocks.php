<?php

namespace App\Console\Commands;

use App\Models\ProductStock;
use App\Models\Warehouse;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Obuchmann\OdooJsonRpc\Odoo;

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
    public function handle(Odoo $odoo)
    {
        $this->info('Starting Odoo Warehouse and Stock Sync...');

        // Step 1: Sync Warehouses
        $this->info('Fetching warehouses...');
        $warehouses = $this->syncWarehouses($odoo);

        if (empty($warehouses)) {
            $this->error('No warehouses found. Aborting stock sync.');
            return 1;
        }

        // Step 2: Sync Product Stocks for each warehouse
        foreach ($warehouses as $warehouse) {
            $this->info("Syncing stocks for warehouse: {$warehouse->name} (ID: {$warehouse->odoo_id})");
            $this->syncProductStocks($odoo, $warehouse);
        }

        $this->info('Sync complete!');
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
                'standard_price', // Using standard_price instead of avg_cost
                'qty_available',
                'free_qty',
                'incoming_qty',
                'outgoing_qty',
                'virtual_available',
            ];

            // Fetch products - limiting to first 100 for testing
            $products = $odoo->model('product.product')
                ->fields($fields)
                ->limit(100)
                ->get();

            $bar = $this->output->createProgressBar(count($products));
            $bar->start();

            foreach ($products as $product) {
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

            $this->info('Synced ' . count($products) . ' products for warehouse ' . $warehouse->name);
        } catch (\Exception $e) {
            $this->error("Failed to fetch stocks for warehouse {$warehouse->name}: " . $e->getMessage());
            Log::error("Odoo Stock Sync Error for warehouse {$warehouse->odoo_id}: " . $e->getMessage());
        }
    }
}
