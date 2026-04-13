<?php

namespace App\Console\Commands;

use App\Models\SalesOrder;
use App\Services\LegacyEndpointService;
use App\Services\LegacySyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncLegacySalesOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'legacy:sync-sales-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync sales orders with legacy V1 data by matching names';

    /**
     * Execute the console command.
     */
    public function handle(LegacyEndpointService $endpointService, LegacySyncService $syncService)
    {
        $this->info('Starting legacy sales order sync...');
        
        $legacyOrders = $endpointService->fetchSalesOrders();
        
        if (empty($legacyOrders)) {
            $this->error('No legacy orders found or failed to fetch.');
            return 1;
        }

        $this->info('Preloading global Mappings to eliminate N+1 queries...');
        $userMap = \App\Models\User::whereNotNull('legacy_id')->pluck('id', 'legacy_id')->toArray();
        $this->info('Preloaded ' . count($userMap) . ' users.');

        $syncedCount = 0;
        $matchedCount = 0;

        $chunks = array_chunk($legacyOrders, 500);

        foreach ($chunks as $chunkIndex => $chunk) {
            $names = array_filter(array_column($chunk, 'name'));
            
            // 🔥 N+1 FIX: Load all 500 Sales Orders + Calculations in exactly 2 DB queries
            $localOrders = SalesOrder::with('commissionCalculation')->whereIn('name', $names)->get()->keyBy('name');

            foreach ($chunk as $legacyOrder) {
                $name = $legacyOrder['name'] ?? null;
                
                if (!$name || !isset($localOrders[$name])) {
                    continue;
                }

                $salesOrder = $localOrders[$name];
                $matchedCount++;
                
                // Update legacy audit fields on the loaded model
                $salesOrder->update([
                    'legacy_last_confirmed_by' => $legacyOrder['last_confirmed_by'] ?? null,
                    'legacy_last_entered_odoo_by' => $legacyOrder['last_entered_odoo_by'] ?? null,
                    'legacy_last_manual_add_by' => $legacyOrder['last_manual_add_by'] ?? null,
                    'legacy_confirmed_by_manager' => isset($legacyOrder['confirmed_by_manager']) ? (bool)$legacyOrder['confirmed_by_manager'] : false,
                    'legacy_is_entered_odoo' => isset($legacyOrder['is_entered_odoo']) ? (bool)$legacyOrder['is_entered_odoo'] : false,
                    'legacy_additional_deduction' => $legacyOrder['additional_deduction'] ?? null,
                    'legacy_manual_notes' => $legacyOrder['manual_notes'] ?? null,
                    'x_studio_commission_paid' => isset($legacyOrder['x_studio_commission_paid']) ? (bool)$legacyOrder['x_studio_commission_paid'] : false,
                    'x_studio_invoice_payment_status' => $legacyOrder['x_studio_invoice_payment_status'] ?? null,
                    'x_studio_payment_type' => $legacyOrder['x_studio_payment_type'] ?? null,
                    'delivery_status' => $legacyOrder['delivery_status'] ?? null,
                    'amount_total' => $legacyOrder['amount_total'] ?? 0,
                    'amount_to_invoice' => $legacyOrder['amount_to_invoice'] ?? 0,
                ]);

                // 🔥 N+1 FIX: Pass the preloaded userMap so it doesn't query Users inside loop
                $syncService->syncLegacyStatus($salesOrder, $userMap);
                
                $syncedCount++;
            }
            
            $this->info("Processed {$syncedCount} orders out of " . count($legacyOrders) . "...");
        }

        $this->info("Golden Sync completed. Matched: $matchedCount, Processed: $syncedCount");
        
        return 0;
    }
}
