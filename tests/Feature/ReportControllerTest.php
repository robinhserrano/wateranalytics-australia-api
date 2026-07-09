<?php

use App\Models\CommissionCalculation;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function reportRoleUser(string $role, array $attrs = []): User
{
    Role::firstOrCreate(['name' => $role]);
    $user = User::factory()->create($attrs);
    $user->assignRole($role);

    return $user;
}

function commissionForUser(User $user, float $amountTotal, float $profit, ?\Carbon\Carbon $createDate = null): CommissionCalculation
{
    static $odooId = 60000;

    $order = SalesOrder::create([
        'odoo_id' => $odooId++,
        'name' => 'SO-'.uniqid(),
        'amount_total' => $amountTotal,
        'state' => 'sale',
        'create_date' => $createDate ?? now(),
    ]);

    return CommissionCalculation::create([
        'sales_order_id' => $order->id,
        'user_id' => $user->id,
        'sales_source' => 'company_lead',
        'payment_type' => 'cash',
        'selling_price' => $amountTotal,
        'profit' => $profit,
        'base_commission' => 0,
        'extra_commission' => 0,
        'manual_adjustment' => 0,
        'final_commission' => 0,
        'status' => 'pending',
    ]);
}

test('a sales person without the right role cannot access reports', function () {
    $user = reportRoleUser('Sales Person');

    $this->actingAs($user)->get(route('reports.index'))->assertForbidden();
});

test('an admin sees totals across every user', function () {
    $admin = reportRoleUser('Admin');
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    commissionForUser($userA, 1000, 200);
    commissionForUser($userB, 2000, 300);

    $response = $this->actingAs($admin)->get(route('reports.index'));

    $response->assertOk();
    $totals = $response->viewData('page')['props']['totals'];
    expect($totals['total_sales'])->toBe(3000.0)
        ->and($totals['total_profit'])->toBe(500.0);
});

test('a sales manager only sees totals for their own team, not unrelated users', function () {
    $manager = reportRoleUser('Sales Manager');
    $report = User::factory()->create(['sales_manager_id' => $manager->id]);
    $outsider = User::factory()->create();

    commissionForUser($manager, 1000, 100);
    commissionForUser($report, 500, 50);
    commissionForUser($outsider, 9000, 9000);

    $response = $this->actingAs($manager)->get(route('reports.index'));

    $response->assertOk();
    $totals = $response->viewData('page')['props']['totals'];
    expect($totals['total_sales'])->toBe(1500.0)
        ->and($totals['total_profit'])->toBe(150.0);
});

test('a sales manager cannot see an outsider even by requesting their id directly', function () {
    $manager = reportRoleUser('Sales Manager');
    $outsider = User::factory()->create();
    commissionForUser($outsider, 9000, 9000);

    $response = $this->actingAs($manager)->get(route('reports.index', ['user_ids' => [$outsider->id]]));

    $response->assertOk();
    $totals = $response->viewData('page')['props']['totals'];
    expect($totals['total_sales'])->toBe(0.0)
        ->and($totals['total_profit'])->toBe(0.0);
});

test('commissions older than 12 months are excluded from the totals', function () {
    $admin = reportRoleUser('Admin');
    $user = User::factory()->create();
    commissionForUser($user, 1000, 100, now()->subMonths(13));
    commissionForUser($user, 500, 50, now());

    $response = $this->actingAs($admin)->get(route('reports.index'));

    $totals = $response->viewData('page')['props']['totals'];
    expect($totals['total_sales'])->toBe(500.0)
        ->and($totals['total_profit'])->toBe(50.0);
});

test('active_reps counts distinct salespeople per month', function () {
    $admin = reportRoleUser('Admin');
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    commissionForUser($userA, 1000, 100, now());
    commissionForUser($userA, 500, 50, now()); // same user, same month, shouldn't double count
    commissionForUser($userB, 2000, 200, now());

    $response = $this->actingAs($admin)->get(route('reports.index'));

    $monthly = collect($response->viewData('page')['props']['monthly']);
    $currentMonth = $monthly->firstWhere('month', now()->format('Y-m'));
    expect($currentMonth['active_reps'])->toBe(2);
});

test('month drill-down returns the orders for that month, scoped like the rest of the report', function () {
    $manager = reportRoleUser('Sales Manager');
    $report = User::factory()->create(['sales_manager_id' => $manager->id]);
    $outsider = User::factory()->create();

    $inMonth = commissionForUser($report, 700, 70, now());
    commissionForUser($outsider, 9000, 9000, now()); // different team, must not appear
    commissionForUser($report, 300, 30, now()->subMonths(2)); // different month, must not appear

    $response = $this->actingAs($manager)->get(route('reports.index', ['month' => now()->format('Y-m')]));

    $response->assertOk();
    $detail = $response->viewData('page')['props']['monthDetail'];
    expect($detail)->not->toBeNull()
        ->and($detail['active_reps'])->toBe(1)
        ->and($detail['total_sales'])->toBe(700.0)
        ->and(collect($detail['orders'])->pluck('id')->all())->toBe([$inMonth->salesOrder->id]);
});

test('a sales manager cannot pull an outsider into the month drill-down by requesting their id', function () {
    $manager = reportRoleUser('Sales Manager');
    $outsider = User::factory()->create();
    commissionForUser($outsider, 9000, 9000, now());

    $response = $this->actingAs($manager)->get(route('reports.index', [
        'user_ids' => [$outsider->id],
        'month' => now()->format('Y-m'),
    ]));

    $detail = $response->viewData('page')['props']['monthDetail'];
    expect($detail['orders'])->toBeEmpty();
});
