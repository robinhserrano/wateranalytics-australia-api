<?php

use App\Console\Commands\SyncOdooSalesOrders;
use App\Models\CommissionCalculation;
use App\Models\Contact;
use App\Models\SalesOrder;
use App\Models\User;
use App\Services\CommissionCalculator;
use Illuminate\Console\OutputStyle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

uses(RefreshDatabase::class);

function callRecalculateCommissionsForSyncedOrders(array $syncedOdooIds): void
{
    $command = new SyncOdooSalesOrders;
    $command->setOutput(new OutputStyle(
        new ArrayInput([]),
        new NullOutput
    ));
    $method = new ReflectionMethod($command, 'recalculateCommissionsForSyncedOrders');
    $method->setAccessible(true);
    $method->invoke($command, new CommissionCalculator, $syncedOdooIds);
}

function makeOrderWithUser(int $odooId, ?string $existingCommissionStatus = null): SalesOrder
{
    $user = User::factory()->create(['self_gen_base' => 1000, 'company_lead_base' => 500, 'commission_split' => 50]);
    $contact = Contact::create(['odoo_id' => $odooId + 10000, 'display_name' => 'Contact '.$odooId, 'user_id' => $user->id]);
    $salesOrder = SalesOrder::create([
        'odoo_id' => $odooId,
        'name' => 'SO-'.$odooId,
        'amount_total' => 1000,
        'x_studio_payment_type' => 'cash',
        'x_studio_sales_source' => 'company_lead',
        'partner_id' => $contact->odoo_id,
    ]);

    if ($existingCommissionStatus !== null) {
        CommissionCalculation::create([
            'sales_order_id' => $salesOrder->id,
            'user_id' => $user->id,
            'sales_source' => 'self_gen',
            'payment_type' => 'cash',
            'selling_price' => 1000,
            'additional_cost' => 0,
            'landing_price' => 0,
            'profit' => 1000,
            'base_commission' => 1000, // stale self-gen base, order is now company_lead
            'extra_commission' => 500,
            'manual_adjustment' => 0,
            'final_commission' => 1500,
            'status' => $existingCommissionStatus,
        ]);
    }

    return $salesOrder;
}

test('recalculates an order with no existing commission', function () {
    $order = makeOrderWithUser(1);

    callRecalculateCommissionsForSyncedOrders([1]);

    expect($order->fresh()->commissionCalculation)->not->toBeNull();
});

test('recalculates an order whose commission is still pending', function () {
    $order = makeOrderWithUser(2, 'pending');

    callRecalculateCommissionsForSyncedOrders([2]);

    // base_commission should now reflect company_lead (500), not the stale self_gen value (1000)
    expect((float) $order->fresh()->commissionCalculation->base_commission)->toBe(500.0)
        ->and($order->fresh()->commissionCalculation->sales_source)->toBe('company_lead');
});

test('does not touch an order whose commission is already approved', function () {
    $order = makeOrderWithUser(3, 'approved');
    $before = $order->fresh()->commissionCalculation->base_commission;

    callRecalculateCommissionsForSyncedOrders([3]);

    expect((float) $order->fresh()->commissionCalculation->base_commission)->toBe((float) $before)
        ->and($order->fresh()->commissionCalculation->status)->toBe('approved');
});

test('does not touch an order whose commission is already paid', function () {
    $order = makeOrderWithUser(4, 'paid');
    $before = $order->fresh()->commissionCalculation->base_commission;

    callRecalculateCommissionsForSyncedOrders([4]);

    expect((float) $order->fresh()->commissionCalculation->base_commission)->toBe((float) $before)
        ->and($order->fresh()->commissionCalculation->status)->toBe('paid');
});

test('ignores orders not in the synced batch', function () {
    $order = makeOrderWithUser(5);

    callRecalculateCommissionsForSyncedOrders([999]);

    expect($order->fresh()->commissionCalculation)->toBeNull();
});
