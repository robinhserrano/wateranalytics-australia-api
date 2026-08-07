<?php

use App\Models\CommissionCalculation;
use App\Models\Contact;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeCalcMissingOrder(int $odooId, ?string $existingStatus = null, bool $confirmedByManager = false, float $manualAdjustment = 0): SalesOrder
{
    $user = User::factory()->create(['self_gen_base' => 1000, 'company_lead_base' => 500, 'commission_split' => 50]);
    $contact = Contact::create(['odoo_id' => $odooId + 20000, 'display_name' => 'Contact '.$odooId, 'user_id' => $user->id]);
    $salesOrder = SalesOrder::create([
        'odoo_id' => $odooId,
        'name' => 'SO-CM-'.$odooId,
        'amount_total' => 1000,
        'x_studio_payment_type' => 'cash',
        'x_studio_sales_source' => 'company_lead',
        'partner_id' => $contact->odoo_id,
    ]);

    if ($existingStatus !== null) {
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
            'manual_adjustment' => $manualAdjustment,
            'final_commission' => 1500 + $manualAdjustment,
            'status' => $existingStatus,
            'confirmed_by_manager' => $confirmedByManager,
        ]);
    }

    return $salesOrder;
}

test('recalculates an order with no existing commission', function () {
    $order = makeCalcMissingOrder(1);

    $this->artisan('commissions:calculate-missing', ['--update-unconfirmed' => true])->assertExitCode(0);

    expect($order->fresh()->commissionCalculation)->not->toBeNull();
});

test('recalculates a pending commission even when confirmed_by_manager is true', function () {
    // This is the exact bug: confirmed_by_manager is a separate sales-manager
    // signoff flag from status, and can be true while status is still pending.
    // The old --update-unconfirmed query gated on confirmed_by_manager and
    // would have wrongly skipped this order.
    $order = makeCalcMissingOrder(2, 'pending', confirmedByManager: true);

    $this->artisan('commissions:calculate-missing', ['--update-unconfirmed' => true])->assertExitCode(0);

    expect((float) $order->fresh()->commissionCalculation->base_commission)->toBe(500.0);
});

test('does not touch an approved commission even with confirmed_by_manager false', function () {
    // The inverse of the bug: confirmed_by_manager=false does NOT mean it's
    // safe to recalculate - an approved commission must never be touched.
    $order = makeCalcMissingOrder(3, 'approved', confirmedByManager: false);
    $before = $order->fresh()->commissionCalculation->base_commission;

    $this->artisan('commissions:calculate-missing', ['--update-unconfirmed' => true])->assertExitCode(0);

    expect((float) $order->fresh()->commissionCalculation->base_commission)->toBe((float) $before);
});

test('does not touch a paid commission', function () {
    $order = makeCalcMissingOrder(4, 'paid');
    $before = $order->fresh()->commissionCalculation->base_commission;

    $this->artisan('commissions:calculate-missing', ['--update-unconfirmed' => true])->assertExitCode(0);

    expect((float) $order->fresh()->commissionCalculation->base_commission)->toBe((float) $before);
});

test('does not touch a pending commission that already has a manual adjustment', function () {
    $order = makeCalcMissingOrder(7, 'pending', manualAdjustment: 100);
    $before = $order->fresh()->commissionCalculation->base_commission;

    $this->artisan('commissions:calculate-missing', ['--update-unconfirmed' => true])->assertExitCode(0);

    expect((float) $order->fresh()->commissionCalculation->base_commission)->toBe((float) $before)
        ->and((float) $order->fresh()->commissionCalculation->manual_adjustment)->toBe(100.0);
});

test('without --update-unconfirmed, only orders with no commission at all are touched', function () {
    $missing = makeCalcMissingOrder(5);
    $pending = makeCalcMissingOrder(6, 'pending');
    $pendingBefore = $pending->fresh()->commissionCalculation->base_commission;

    $this->artisan('commissions:calculate-missing')->assertExitCode(0);

    expect($missing->fresh()->commissionCalculation)->not->toBeNull()
        ->and((float) $pending->fresh()->commissionCalculation->base_commission)->toBe((float) $pendingBefore);
});
