<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function actingAsRoleFor(string $role): User
{
    Role::firstOrCreate(['name' => $role]);
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

// --- role:Admin group ---

test('a non-admin cannot access an admin-only route', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('products.index'))->assertForbidden();
});

test('an admin can access an admin-only route', function () {
    $admin = actingAsRoleFor('Admin');

    $this->actingAs($admin)->get(route('products.index'))->assertOk();
});

// --- role:Admin|Sales Manager|Sales Team Manager group ---

test('a plain user cannot access the my-team route', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('my-team.index'))->assertForbidden();
});

test('a sales manager can access the my-team route', function () {
    $manager = actingAsRoleFor('Sales Manager');

    $this->actingAs($manager)->get(route('my-team.index'))->assertOk();
});

// --- role:Admin|Account Officer group ---

test('a non-account-officer cannot mark a commission entered to odoo', function () {
    $user = User::factory()->create();
    $commission = \App\Models\CommissionCalculation::create([
        'sales_order_id' => \App\Models\SalesOrder::create(['odoo_id' => 50001, 'name' => 'SO-A'])->id,
        'user_id' => User::factory()->create()->id,
        'sales_source' => 'company_lead',
        'payment_type' => 'cash',
        'selling_price' => 1000,
        'profit' => 200,
        'base_commission' => 100,
        'extra_commission' => 50,
        'manual_adjustment' => 0,
        'final_commission' => 150,
        'status' => 'approved',
    ]);

    $this->actingAs($user)->post(route('commissions.mark-odoo', $commission))->assertForbidden();
});

// --- privilege-escalation check on users/{user} update ---
// UserController::update() now blocks non-admins from editing anyone but
// themselves, and strips privileged fields (role_names, sales_manager_id,
// commission rates) from non-admin requests entirely.
test('a plain user cannot update another user account', function () {
    $attacker = User::factory()->create();
    $victim = User::factory()->create();

    $this->actingAs($attacker)->put(route('users.update', $victim), [
        'name' => $victim->name,
        'email' => $victim->email,
    ])->assertForbidden();
});

test('a plain user cannot grant themselves the Admin role via their own update request', function () {
    Role::firstOrCreate(['name' => 'Admin']);
    $user = User::factory()->create();

    $this->actingAs($user)->put(route('users.update', $user), [
        'name' => $user->name,
        'email' => $user->email,
        'role_names' => ['Admin'],
    ]);

    expect($user->fresh()->hasRole('Admin'))->toBeFalse();
});
