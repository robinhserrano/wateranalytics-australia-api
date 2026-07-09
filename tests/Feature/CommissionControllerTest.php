<?php

use App\Models\CommissionCalculation;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function actingAsRole(string $role): User
{
    Role::firstOrCreate(['name' => $role]);
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

function makeCommission(array $orderAttrs = [], array $commissionAttrs = []): CommissionCalculation
{
    static $odooId = 40000;

    $order = SalesOrder::create(array_merge([
        'odoo_id' => $odooId++,
        'name' => 'SO-'.uniqid(),
        'amount_total' => 1000,
    ], $orderAttrs));

    return CommissionCalculation::create(array_merge([
        'sales_order_id' => $order->id,
        'user_id' => User::factory()->create()->id,
        'sales_source' => 'company_lead',
        'payment_type' => 'cash',
        'selling_price' => 1000,
        'profit' => 200,
        'base_commission' => 100,
        'extra_commission' => 50,
        'manual_adjustment' => 0,
        'final_commission' => 150,
        'status' => 'pending',
    ], $commissionAttrs));
}

// --- confirm(): delivery-status gate ---

test('a sales manager cannot confirm a commission until the order is fully delivered', function () {
    $manager = actingAsRole('Sales Manager');
    $commission = makeCommission(['delivery_status' => 'partial']);

    $this->actingAs($manager)
        ->post(route('commissions.confirm', $commission))
        ->assertRedirect();

    expect($commission->fresh()->confirmed_by_manager)->toBeFalse();
});

test('a sales manager can confirm a commission once the order is fully delivered', function () {
    $manager = actingAsRole('Sales Manager');
    $commission = makeCommission(['delivery_status' => 'full']);

    $this->actingAs($manager)->post(route('commissions.confirm', $commission));

    expect($commission->fresh()->confirmed_by_manager)->toBeTrue();
    $this->assertDatabaseHas('commission_approvals', [
        'commission_calculation_id' => $commission->id,
        'action' => 'confirmed',
    ]);
});

test('an admin can confirm a commission regardless of delivery status', function () {
    $admin = actingAsRole('Admin');
    $commission = makeCommission(['delivery_status' => 'pending']);

    $this->actingAs($admin)->post(route('commissions.confirm', $commission));

    expect($commission->fresh()->confirmed_by_manager)->toBeTrue();
});

// --- markAsPaid(): status guard ---

test('a pending commission cannot be marked as paid', function () {
    $admin = actingAsRole('Admin');
    $commission = makeCommission([], ['status' => 'pending']);

    $this->actingAs($admin)
        ->post(route('commissions.mark-paid', $commission))
        ->assertStatus(422);

    expect($commission->fresh()->status)->toBe('pending');
});

test('an approved commission can be marked as paid', function () {
    $admin = actingAsRole('Admin');
    $commission = makeCommission([], ['status' => 'approved']);

    $this->actingAs($admin)->post(route('commissions.mark-paid', $commission));

    expect($commission->fresh()->status)->toBe('paid');
});

// --- reject(): reason validation ---

test('rejecting a commission requires a reason of at least 10 characters', function () {
    $admin = actingAsRole('Admin');
    $commission = makeCommission();

    $this->actingAs($admin)
        ->post(route('commissions.reject', $commission), ['reason' => 'too short'])
        ->assertSessionHasErrors('reason');

    expect($commission->fresh()->status)->toBe('pending');
});

test('rejecting a commission with a valid reason updates its status', function () {
    $admin = actingAsRole('Admin');
    $commission = makeCommission();

    $this->actingAs($admin)->post(route('commissions.reject', $commission), [
        'reason' => 'Order was cancelled by customer',
    ]);

    expect($commission->fresh()->status)->toBe('rejected');
});

// --- bulkApprove(): only touches pending commissions ---

test('bulk approve only approves commissions that are still pending', function () {
    $admin = actingAsRole('Admin');
    $pending = makeCommission([], ['status' => 'pending']);
    $alreadyApproved = makeCommission([], ['status' => 'approved']);
    $rejected = makeCommission([], ['status' => 'rejected']);

    $this->actingAs($admin)->post(route('commissions.bulk-approve'), [
        'commission_ids' => [$pending->id, $alreadyApproved->id, $rejected->id],
    ]);

    expect($pending->fresh()->status)->toBe('approved')
        ->and($rejected->fresh()->status)->toBe('rejected');
    $this->assertDatabaseCount('commission_approvals', 1);
});
