<?php

use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    // hasPermissionTo() throws PermissionDoesNotExist if the permission record
    // isn't in the DB at all (regardless of assignment), so seed every permission
    // the controller checks up front.
    foreach ([
        'view-all-sales-orders',
        'view-team-sales-orders',
        'view-own-sales-orders',
        'view-installer',
    ] as $permission) {
        Permission::firstOrCreate(['name' => $permission]);
    }
});

function grantPermission(User $user, string $permission): void
{
    $user->givePermissionTo($permission);
}

function makeIndexableSalesOrder(array $attributes = []): SalesOrder
{
    static $odooId = 20000;

    return SalesOrder::create(array_merge([
        'odoo_id' => $odooId++,
        'name' => 'SO-'.uniqid(),
        'amount_total' => 1000,
        'state' => 'sale',
    ], $attributes));
}

// --- index() permission scoping ---

test('a salesperson without special permissions only sees their own sales orders in the index', function () {
    $me = User::factory()->create(['odoo_user_id' => 501]);
    $stranger = User::factory()->create(['odoo_user_id' => 502]);

    $mine = makeIndexableSalesOrder(['user_id' => 501]);
    $notMine = makeIndexableSalesOrder(['user_id' => 502]);

    $response = $this->actingAs($me)->get(route('sales-orders.index'));

    $response->assertOk();
    $ids = collect($response->viewData('page')['props']['salesOrders']['data'])->pluck('id');

    expect($ids)->toContain($mine->id)
        ->and($ids)->not->toContain($notMine->id);
});

test('a manager with view-team-sales-orders sees orders belonging to their reports', function () {
    $manager = User::factory()->create();
    grantPermission($manager, 'view-team-sales-orders');
    $report = User::factory()->create(['sales_manager_id' => $manager->id, 'odoo_user_id' => 601]);
    $stranger = User::factory()->create(['odoo_user_id' => 602]);

    $teamOrder = makeIndexableSalesOrder(['user_id' => 601]);
    $outsideOrder = makeIndexableSalesOrder(['user_id' => 602]);

    $response = $this->actingAs($manager)->get(route('sales-orders.index'));

    $response->assertOk();
    $ids = collect($response->viewData('page')['props']['salesOrders']['data'])->pluck('id');

    expect($ids)->toContain($teamOrder->id)
        ->and($ids)->not->toContain($outsideOrder->id);
});

test('a user with view-all-sales-orders sees every order regardless of ownership', function () {
    $admin = User::factory()->create();
    grantPermission($admin, 'view-all-sales-orders');
    makeIndexableSalesOrder(['user_id' => 701]);
    makeIndexableSalesOrder(['user_id' => 702]);

    $response = $this->actingAs($admin)->get(route('sales-orders.index'));

    $response->assertOk();
    $ids = collect($response->viewData('page')['props']['salesOrders']['data'])->pluck('id');

    expect($ids)->toHaveCount(2);
});

// --- show() authorization ---

test('a salesperson can view their own sales order', function () {
    $me = User::factory()->create(['odoo_user_id' => 801]);
    $order = makeIndexableSalesOrder(['user_id' => 801]);

    $this->actingAs($me)->get(route('sales-orders.show', $order))->assertOk();
});

test('a salesperson cannot view a sales order they do not own', function () {
    $me = User::factory()->create(['odoo_user_id' => 801]);
    $other = User::factory()->create(['odoo_user_id' => 802]);
    $order = makeIndexableSalesOrder(['user_id' => 802]);

    $this->actingAs($me)->get(route('sales-orders.show', $order))->assertForbidden();
});

test('a manager can view a sales order belonging to a team member', function () {
    $manager = User::factory()->create();
    grantPermission($manager, 'view-team-sales-orders');
    $report = User::factory()->create(['sales_manager_id' => $manager->id]);
    $order = makeIndexableSalesOrder();
    // show() resolves team ownership via the commission calculation's user_id
    // (a local user ID), not the raw Odoo user_id field on the sales order.
    \App\Models\CommissionCalculation::create([
        'sales_order_id' => $order->id,
        'user_id' => $report->id,
        'sales_source' => 'company_lead',
        'payment_type' => 'cash',
        'selling_price' => 1000,
        'profit' => 200,
        'base_commission' => 100,
        'extra_commission' => 50,
        'manual_adjustment' => 0,
        'final_commission' => 150,
        'status' => 'pending',
    ]);

    $this->actingAs($manager)->get(route('sales-orders.show', $order))->assertOk();
});

test('a manager cannot view a sales order outside their team', function () {
    $manager = User::factory()->create();
    grantPermission($manager, 'view-team-sales-orders');
    $outsider = User::factory()->create(['odoo_user_id' => 902]);
    $order = makeIndexableSalesOrder(['user_id' => 902]);

    $this->actingAs($manager)->get(route('sales-orders.show', $order))->assertForbidden();
});

test('a user with view-all-sales-orders can view any sales order', function () {
    $admin = User::factory()->create();
    grantPermission($admin, 'view-all-sales-orders');
    $order = makeIndexableSalesOrder(['user_id' => 1001]);

    $this->actingAs($admin)->get(route('sales-orders.show', $order))->assertOk();
});
