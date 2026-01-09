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

        $this->info('Fetched ' . count($legacyOrders) . ' legacy orders. Processing...');

        $syncedCount = 0;
        $matchedCount = 0;

        foreach ($legacyOrders as $legacyOrder) {
            $name = $legacyOrder['name'] ?? null;
            
            if (!$name) {
                continue;
            }

            // Find local sales order by name
            $salesOrder = SalesOrder::where('name', $name)->first();

            if ($salesOrder) {
                $matchedCount++;
                
                // Update legacy audit fields
                // Map legacy fields to our new columns
                // Expected legacy fields:
                // confirmed_by_manager, is_entered_odoo, last_confirmed_by, last_entered_odoo_by, last_manual_add_by
                
                $salesOrder->update([
                    'legacy_last_confirmed_by' => $legacyOrder['last_confirmed_by'] ?? null,
                    'legacy_last_entered_odoo_by' => $legacyOrder['last_entered_odoo_by'] ?? null,
                    'legacy_last_manual_add_by' => $legacyOrder['last_manual_add_by'] ?? null,
                    'legacy_confirmed_by_manager' => isset($legacyOrder['confirmed_by_manager']) ? (bool)$legacyOrder['confirmed_by_manager'] : false,
                    'legacy_is_entered_odoo' => isset($legacyOrder['is_entered_odoo']) ? (bool)$legacyOrder['is_entered_odoo'] : false,
                    'x_studio_commission_paid' => isset($legacyOrder['x_studio_commission_paid']) ? (bool)$legacyOrder['x_studio_commission_paid'] : false,
                    'x_studio_invoice_payment_status' => $legacyOrder['x_studio_invoice_payment_status'] ?? null,
                    'x_studio_payment_type' => $legacyOrder['x_studio_payment_type'] ?? null,
                    'delivery_status' => $legacyOrder['delivery_status'] ?? null,
                    'amount_total' => $legacyOrder['amount_total'] ?? 0,
                    'amount_to_invoice' => $legacyOrder['amount_to_invoice'] ?? 0,
                ]);

                // Trigger the sync service logic to update approvals
                $syncService->syncLegacyStatus($salesOrder);
                
                $syncedCount++;
                if ($syncedCount % 10 === 0) {
                    $this->line("Processed $syncedCount orders...");
                }
            }
        }

        $this->info("Sync completed. Matched: $matchedCount, Synced: $syncedCount");
        
        return 0;
    }
}
