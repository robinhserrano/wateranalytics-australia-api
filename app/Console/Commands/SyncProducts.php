<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Obuchmann\OdooJsonRpc\Odoo;
use App\Services\SyncLogger;

class SyncProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'odoo:sync-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Products from Odoo';

    /**
     * Execute the console command.
     */
    public function handle(Odoo $odoo, SyncLogger $logger)
    {
        $log = $logger->start('odoo:sync-products');
        $this->info('Starting Odoo Products Sync...');

        try {
            $allRecords = [];
            $offset = 0;
            $limit = 1000;
            $hasMoreRecords = true;
            $batchNumber = 1;

            while ($hasMoreRecords) {
                $this->info("Fetching products batch #$batchNumber (offset: $offset, limit: $limit)");

                // Using web_search_read to match the Flutter code pattern
                $response = $odoo->model('product.template')
                    ->fields([
                        'id',
                        'product_variant_count',
                        'currency_id',
                        'activity_state',
                        'categ_id',
                        'write_date',
                        'name',
                        'default_code',
                        'list_price',
                        'qty_available',
                        'uom_id',
                        'product_properties',
                        'type',
                        'show_on_hand_qty_status_button',
                    ])
                    ->where('sale_ok', '=', true)
                    ->limit($limit)
                    ->offset($offset)
                    ->get();

                if (empty($response)) {
                    $this->info("No more product records found in batch #$batchNumber");
                    $hasMoreRecords = false;
                    continue;
                }

                $bar = $this->output->createProgressBar(count($response));
                $bar->start();

                foreach ($response as $product) {
                    // Extract category info
                    $categId = null;
                    $categName = null;
                    if (!empty($product->categ_id) && is_array($product->categ_id)) {
                        $categId = $product->categ_id[0];
                        $categName = $product->categ_id[1];
                    }

                    // Extract currency info
                    $currencyId = null;
                    $currencyName = null;
                    if (!empty($product->currency_id) && is_array($product->currency_id)) {
                        $currencyId = $product->currency_id[0];
                        $currencyName = $product->currency_id[1];
                    }

                    // Extract UOM info
                    $uomId = null;
                    $uomName = null;
                    if (!empty($product->uom_id) && is_array($product->uom_id)) {
                        $uomId = $product->uom_id[0];
                        $uomName = $product->uom_id[1];
                    }

                    Product::updateOrCreate(
                        ['odoo_id' => $product->id],
                        [
                            'name' => $product->name ?? null,
                            'default_code' => $product->default_code ?? null,
                            'product_variant_count' => $product->product_variant_count ?? 0,
                            'categ_id' => $categId,
                            'categ_name' => $categName,
                            'currency_id' => $currencyId,
                            'currency_name' => $currencyName,
                            'uom_id' => $uomId,
                            'uom_name' => $uomName,
                            'list_price' => $product->list_price ?? 0,
                            'qty_available' => $product->qty_available ?? 0,
                            'product_properties' => $product->product_properties ?? null,
                            'type' => $product->type ?? null,
                            'activity_state' => $product->activity_state ?? null,
                            'show_on_hand_qty_status_button' => $product->show_on_hand_qty_status_button ?? false,
                            'write_date' => $product->write_date ?? null,
                        ]
                    );

                    $bar->advance();
                }

                $bar->finish();
                $this->newLine();

                $allRecords = array_merge($allRecords, $response);

                if (count($response) < $limit) {
                    $this->info("Reached end of data (received " . count($response) . " < limit $limit)");
                    $hasMoreRecords = false;
                } else {
                    $offset += $limit;
                    $batchNumber++;
                }
            }

            $this->info('Successfully synced all products. Total records: ' . count($allRecords));
            $logger->complete($log, count($allRecords));
            return 0;
        } catch (\Exception $e) {
            $this->error('Error syncing products: ' . $e->getMessage());
            Log::error('Odoo Products Sync Error: ' . $e->getMessage());
            $logger->fail($log, $e);
            return 1;
        }
    }
}
