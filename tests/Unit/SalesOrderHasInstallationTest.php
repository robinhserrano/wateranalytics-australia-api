<?php

use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeInstallationSalesOrder(array $attributes = []): SalesOrder
{
    static $odooId = 9000;

    return SalesOrder::create(array_merge([
        'odoo_id' => $odooId++,
        'name' => 'SO-'.uniqid(),
        'amount_total' => 1000,
    ], $attributes));
}

test('has_installation is false when there is no installer assigned', function () {
    $salesOrder = makeInstallationSalesOrder(['installer_id' => null]);

    expect($salesOrder->has_installation)->toBeFalse();
});

test('has_installation is true when installer is assigned and lines are not loaded', function () {
    $salesOrder = makeInstallationSalesOrder(['installer_id' => 42]);

    expect($salesOrder->relationLoaded('lines'))->toBeFalse()
        ->and($salesOrder->has_installation)->toBeTrue();
});

test('has_installation is true when installer is assigned and no line is supply-only', function () {
    $salesOrder = makeInstallationSalesOrder(['installer_id' => 42]);
    SalesOrderLine::create([
        'odoo_id' => 8001,
        'sales_order_id' => $salesOrder->id,
        'odoo_order_id' => $salesOrder->odoo_id,
        'is_supply_only' => false,
    ]);
    $salesOrder->load('lines');

    expect($salesOrder->has_installation)->toBeTrue();
});

test('has_installation is false when installer is assigned but a line is flagged supply-only', function () {
    $salesOrder = makeInstallationSalesOrder(['installer_id' => 42]);
    SalesOrderLine::create([
        'odoo_id' => 8002,
        'sales_order_id' => $salesOrder->id,
        'odoo_order_id' => $salesOrder->odoo_id,
        'is_supply_only' => true,
    ]);
    $salesOrder->load('lines');

    expect($salesOrder->has_installation)->toBeFalse();
});
