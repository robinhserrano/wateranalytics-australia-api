<?php

use App\Console\Commands\SyncOdooSalesOrders;
use App\Models\CommissionCalculation;
use App\Models\SalesOrder;
use App\Models\SyncLog;
use App\Models\User;
use App\Services\CommissionCalculator;
use App\Services\OdooClient;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

// On 2026-10-19 the first reconcile found 38 pending commissions (~$20k positive) on orders cancelled or
// deleted in Odoo. The reconcile now rejects those automatically, and the sales sync undoes that if the
// order is reinstated in Odoo. Approved/paid/manual-rejected/manually-adjusted ones are never touched.

function orderWithCommission(int $odooId, string $state, string $status, array $orderAttrs = [], array $calcAttrs = []): CommissionCalculation
{
    $user = User::factory()->create(['self_gen_base' => 1000, 'company_lead_base' => 500, 'commission_split' => 50]);
    $order = SalesOrder::create(array_merge(['odoo_id' => $odooId, 'name' => "S{$odooId}", 'state' => $state, 'tag_ids' => [2], 'amount_total' => 1000], $orderAttrs));

    return CommissionCalculation::create(array_merge([
        'sales_order_id' => $order->id, 'user_id' => $user->id, 'sales_source' => 'company_lead', 'payment_type' => 'cash',
        'selling_price' => 1000, 'additional_cost' => 0, 'landing_price' => 0, 'profit' => 1000,
        'base_commission' => 500, 'extra_commission' => 500, 'manual_adjustment' => 0, 'final_commission' => 1000, 'status' => $status,
    ], $calcAttrs));
}

// Odoo agrees with every local tag-2 `sale` order (so the reconcile itself changes nothing and the 20%
// brake stays out of the way); only the commission auto-reject is under test here
function reconcileWithEmptyOdoo(): void
{
    test()->mock(OdooClient::class, function ($mock) {
        $mock->shouldReceive('search')->andReturnUsing(fn () => SalesOrder::where('state', 'sale')->pluck('odoo_id')
            ->filter(fn ($id) => in_array(2, SalesOrder::where('odoo_id', $id)->value('tag_ids') ?? []))->values()->all());
        $mock->shouldReceive('searchRead')->andReturnUsing(fn ($model, $domain) => SalesOrder::whereIn('odoo_id', $domain[0][2])->get()
            ->map(fn ($o) => (object) ['id' => $o->odoo_id, 'state' => $o->state, 'tag_ids' => $o->tag_ids])->all());
    });
    test()->artisan('odoo:reconcile-sales')->assertExitCode(0);
}

test('pending commissions on cancelled and deleted orders are auto-rejected, including earlier ones', function () {
    $cancelled = orderWithCommission(1, 'cancel', 'pending');
    $deleted = orderWithCommission(2, 'cancel', 'pending', ['odoo_missing_at' => now()]);
    foreach (range(3, 10) as $id) {
        orderWithCommission($id, 'sale', 'approved'); // keeps the 20% brake out of the way
    }

    reconcileWithEmptyOdoo();

    foreach ([$cancelled, $deleted] as $calc) {
        expect($calc->fresh())
            ->status->toBe('rejected')
            ->rejected_by->toBeNull()
            ->rejection_reason->toBe(CommissionCalculation::AUTO_REJECT_REASON)
            ->rejected_at->not->toBeNull();
    }
    expect(SyncLog::latest('id')->first()->message)->toContain('auto-rejected 2 pending commission(s)');
});

test('approved, paid, manually rejected and manually adjusted commissions are left alone', function () {
    $manager = User::factory()->create();
    $approved = orderWithCommission(1, 'cancel', 'approved');
    $paid = orderWithCommission(2, 'cancel', 'paid');
    $manualReject = orderWithCommission(3, 'cancel', 'rejected', [], ['rejected_by' => $manager->id, 'rejection_reason' => 'duplicate']);
    $adjusted = orderWithCommission(4, 'cancel', 'pending', [], ['manual_adjustment' => 150]);
    foreach (range(5, 12) as $id) {
        orderWithCommission($id, 'sale', 'approved');
    }

    reconcileWithEmptyOdoo();

    expect($approved->fresh()->status)->toBe('approved')
        ->and($paid->fresh()->status)->toBe('paid')
        ->and($manualReject->fresh())->rejection_reason->toBe('duplicate')
        ->and($adjusted->fresh()->status)->toBe('pending')
        ->and(SyncLog::latest('id')->first()->message)->toContain('1 manually adjusted one(s) left for review');
});

test('untagged or reverted-to-quotation orders keep their pending commission', function () {
    $untagged = orderWithCommission(1, 'sale', 'pending', ['tag_ids' => [5]]);
    $quotation = orderWithCommission(2, 'draft', 'pending');
    foreach (range(3, 12) as $id) {
        orderWithCommission($id, 'sale', 'approved');
    }

    reconcileWithEmptyOdoo();

    expect($untagged->fresh()->status)->toBe('pending')
        ->and($quotation->fresh()->status)->toBe('pending');
});

test('an order reinstated in Odoo gets its auto-rejected commission back and recalculated; a manual rejection stays', function () {
    $manager = User::factory()->create();
    $auto = orderWithCommission(1, 'sale', 'rejected', [], ['rejection_reason' => CommissionCalculation::AUTO_REJECT_REASON, 'rejected_at' => now()]);
    $manual = orderWithCommission(2, 'sale', 'rejected', [], ['rejected_by' => $manager->id, 'rejection_reason' => 'wrong rep']);

    $command = new SyncOdooSalesOrders;
    $command->setOutput(new OutputStyle(new ArrayInput([]), new NullOutput));
    $method = new ReflectionMethod($command, 'recalculateCommissionsForSyncedOrders');
    $method->setAccessible(true);
    $method->invoke($command, new CommissionCalculator, [1, 2]); // both back in the synced (in-scope) set

    expect($auto->fresh())->status->toBe('pending')->rejection_reason->toBeNull()
        ->and($manual->fresh())->status->toBe('rejected')->rejection_reason->toBe('wrong rep');
});
