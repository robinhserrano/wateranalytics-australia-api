<?php

use App\Models\SalesOrder;
use App\Services\LegacyEndpointService;
use App\Services\LegacySyncService;
use Illuminate\Support\Facades\Log;

$endpointService = app(LegacyEndpointService::class);
$syncService = app(LegacySyncService::class);

echo "Fetching legacy orders...\n";
$legacyOrders = $endpointService->fetchSalesOrders();

if (empty($legacyOrders)) {
    echo "No legacy orders found.\n";
    exit;
}

echo "Found " . count($legacyOrders) . " orders. Searching for a match...\n";

$found = false;
foreach ($legacyOrders as $legacyOrder) {
    if (!isset($legacyOrder['name'])) continue;
    
    $name = $legacyOrder['name'];
    $salesOrder = SalesOrder::where('name', $name)->first();
    
    if ($salesOrder) {
        echo "Match found for order: {$name}\n";
        echo "Legacy Order Keys: " . implode(', ', array_keys($legacyOrder)) . "\n";
        echo "Legacy x_studio_commission_paid: " . ($legacyOrder['x_studio_commission_paid'] ?? 'N/A') . "\n";
        echo "Legacy confirmed_by_manager: " . ($legacyOrder['confirmed_by_manager'] ?? 'N/A') . "\n";
        echo "Legacy last_confirmed_by: " . ($legacyOrder['last_confirmed_by'] ?? 'N/A') . "\n";
        echo "Updating local audit fields...\n";
        
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

        echo "Triggering sync service...\n";
        $syncService->syncLegacyStatus($salesOrder);
        
        echo "Sync complete for {$name}.\n";
        $found = true;
        
        // Show the updated status
        $salesOrder->refresh();
        $calc = $salesOrder->commissionCalculation;
        echo "\nResults:\n";
        echo "- Name: {$salesOrder->name}\n";
        echo "- Commission Paid (x_studio): " . ($salesOrder->x_studio_commission_paid ? 'YES' : 'NO') . "\n";
        echo "- Legacy Confirmed: " . ($salesOrder->legacy_confirmed_by_manager ? 'YES' : 'NO') . "\n";
        echo "- Legacy Entered Odoo: " . ($salesOrder->legacy_is_entered_odoo ? 'YES' : 'NO') . "\n";
        if ($calc) {
            echo "- CC Status: {$calc->status}\n";
            echo "- CC Confirmed Manager: " . ($calc->confirmed_by_manager ? 'YES' : 'NO') . "\n";
            echo "- CC Entered Odoo: " . ($calc->entered_to_odoo ? 'YES' : 'NO') . "\n";
            echo "- Approvals created: " . $calc->approvals()->count() . "\n";
            foreach ($calc->approvals as $approval) {
                echo "  * Action: {$approval->action} (by User #{$approval->approver_id})\n";
            }
        } else {
            echo "- No Commission Calculation found for this order.\n";
        }
        
        break; // Only one for testing
    }
}

if (!$found) {
    echo "No local match found for any of the first few legacy orders. Check if the names match Odoo names.\n";
    // Let's print the first legacy name to help debugging
    echo "Example Legacy Name: " . ($legacyOrders[0]['name'] ?? 'N/A') . "\n";
}
