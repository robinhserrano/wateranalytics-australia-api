<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\SyncLog;
use Carbon\Carbon;
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
    protected $signature = 'odoo:sync-products {--all : Force sync all records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Products from Odoo (Optimized)';

    /**
     * Execute the console command.
     */
    public function handle(Odoo $odoo, SyncLogger $logger)
    {
        $log = $logger->start($this->signature);
        $this->info('Starting Odoo Products Sync (Optimized)...');

        $lastSync = null;
        if (!$this->option('all')) {
            $lastSyncRecord = SyncLog::where('command', $this->signature)
                ->where('status', 'completed')
                ->latest('completed_at')
                ->first();
            
            if ($lastSyncRecord) {
                $lastSync = $lastSyncRecord->completed_at;
                $this->info("Performing delta sync since: " . $lastSync->toDateTimeString());
            }
        }

        try {
            $offset = 0;
            $limit = 1000;
            $totalSynced = 0;

            $domain = [['sale_ok', '=', true]];
            if ($lastSync) {
                $domain[] = ['write_date', '>', $lastSync->toDateTimeString()];
            }

            $fields = [
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
            ];

            do {
                $this->info("Fetching products batch (offset: $offset, limit: $limit)");

                $specification = [];
                foreach ($fields as $field) {
                    $specification[$field] = (object)[];
                }

                $response = $odoo->executeKw('product.template', 'web_search_read', [
                    $domain,
                    $specification,
                    $offset,
                    $limit,
                    'id desc'
                ]);

                $products = $response->records ?? (is_array($response) ? ($response['records'] ?? []) : []);

                if (empty($products)) {
                    break;
                }

                $syncData = [];
                foreach ($products as $product) {
                    $product = (object)$product;

                    // Extract category info
                    $categId = $product->categ_id->id ?? null;
                    $categName = $product->categ_id->display_name ?? null;

                    // Extract currency info
                    $currencyId = $product->currency_id->id ?? null;
                    $currencyName = $product->currency_id->display_name ?? null;

                    // Extract UOM info
                    $uomId = $product->uom_id->id ?? null;
                    $uomName = $product->uom_id->display_name ?? null;

                    $syncData[] = [
                        'odoo_id' => $product->id,
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
                        'product_properties' => is_array($product->product_properties) ? json_encode($product->product_properties) : ($product->product_properties ?? null),
                        'type' => $product->type ?? null,
                        'activity_state' => $product->activity_state ?? null,
                        'show_on_hand_qty_status_button' => $product->show_on_hand_qty_status_button ?? false,
                        'write_date' => $product->write_date ?? null,
                        'updated_at' => Carbon::now(),
                    ];
                }

                $this->info("Upserting " . count($syncData) . " products...");
                Product::upsert($syncData, ['odoo_id'], [
                    'name', 'default_code', 'product_variant_count', 'categ_id', 'categ_name',
                    'currency_id', 'currency_name', 'uom_id', 'uom_name', 'list_price',
                    'qty_available', 'product_properties', 'type', 'activity_state',
                    'show_on_hand_qty_status_button', 'write_date', 'updated_at'
                ]);

                $totalSynced += count($products);
                $offset += $limit;

                if (count($products) < $limit) {
                    break;
                }
            } while (true);

            $this->info('Successfully synced all products. Total records: ' . $totalSynced);
            $logger->complete($log, $totalSynced, $lastSync ? "Incremental sync completed." : "Full sync completed.");
            return 0;

        } catch (\Exception $e) {
            $this->error('Error syncing products: ' . $e->getMessage());
            Log::error('Odoo Products Sync Error: ' . $e->getMessage());
            $logger->fail($log, $e);
            return 1;
        }
    }
}
