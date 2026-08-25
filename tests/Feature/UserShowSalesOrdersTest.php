<?php

use App\Models\Contact;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeShowableSalesOrder(array $attributes = []): SalesOrder
{
    static $odooId = 30000;

    return SalesOrder::create(array_merge([
        'odoo_id' => $odooId++,
        'name' => 'SO-'.uniqid(),
        'amount_total' => 1000,
        'state' => 'sale',
        'create_date' => now(),
    ], $attributes));
}

test('order history includes orders matched via the assigned contact partner id', function () {
    $user = User::factory()->create();
    Contact::create(['odoo_id' => 498, 'user_id' => $user->id, 'display_name' => $user->name]);

    $order = makeShowableSalesOrder(['salesperson_partner_id' => 498]);

    $response = $this->actingAs($user)->get(route('users.show', $user));

    $response->assertOk();
    $ids = collect($response->viewData('page')['props']['salesOrders'])->pluck('id');
    expect($ids)->toContain($order->id);
});

test('order history includes orders resolved only via raw Odoo user_id, not linked to any assigned contact', function () {
    $user = User::factory()->create(['odoo_user_id' => 601]);
    Contact::create(['odoo_id' => 498, 'user_id' => $user->id, 'display_name' => $user->name]);

    // Not linked to the user's contact partner id, but resolved via user_id like the commission engine does.
    $order = makeShowableSalesOrder(['salesperson_partner_id' => 999999, 'user_id' => 601]);

    $response = $this->actingAs($user)->get(route('users.show', $user));

    $response->assertOk();
    $ids = collect($response->viewData('page')['props']['salesOrders'])->pluck('id');
    expect($ids)->toContain($order->id);
});

test('order history includes orders resolved only via raw Odoo salesperson_id', function () {
    $user = User::factory()->create(['odoo_salesperson_id' => 701]);
    Contact::create(['odoo_id' => 498, 'user_id' => $user->id, 'display_name' => $user->name]);

    $order = makeShowableSalesOrder(['salesperson_partner_id' => 999999, 'user_id' => 701]);

    $response = $this->actingAs($user)->get(route('users.show', $user));

    $response->assertOk();
    $ids = collect($response->viewData('page')['props']['salesOrders'])->pluck('id');
    expect($ids)->toContain($order->id);
});

test('order history includes orders resolved only via salesperson name match', function () {
    $user = User::factory()->create(['name' => 'Bronwyn Parnell']);
    Contact::create(['odoo_id' => 498, 'user_id' => $user->id, 'display_name' => $user->name]);

    $order = makeShowableSalesOrder(['salesperson_partner_id' => 999999, 'user_name' => 'Bronwyn Parnell']);

    $response = $this->actingAs($user)->get(route('users.show', $user));

    $response->assertOk();
    $ids = collect($response->viewData('page')['props']['salesOrders'])->pluck('id');
    expect($ids)->toContain($order->id);
});

test('order history excludes orders belonging to a different salesperson', function () {
    $user = User::factory()->create(['odoo_user_id' => 601, 'name' => 'Bronwyn Parnell']);
    Contact::create(['odoo_id' => 498, 'user_id' => $user->id, 'display_name' => $user->name]);

    $notMine = makeShowableSalesOrder([
        'salesperson_partner_id' => 999999,
        'user_id' => 999,
        'user_name' => 'Someone Else',
    ]);

    $response = $this->actingAs($user)->get(route('users.show', $user));

    $response->assertOk();
    $ids = collect($response->viewData('page')['props']['salesOrders'])->pluck('id');
    expect($ids)->not->toContain($notMine->id);
});
