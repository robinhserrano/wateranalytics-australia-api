<?php

use App\Models\Contact;
use App\Models\LandingPrice;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use App\Models\User;
use App\Services\CommissionCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeCalculator(): CommissionCalculator
{
    return new CommissionCalculator();
}

function makeSalesOrder(array $attributes = []): SalesOrder
{
    static $odooId = 1000;

    return SalesOrder::create(array_merge([
        'odoo_id' => $odooId++,
        'name' => 'SO-'.uniqid(),
        'amount_total' => 1000,
        'x_studio_payment_type' => 'cash',
        'x_studio_sales_source' => 'company_lead',
    ], $attributes));
}

function makeProduct(array $attributes = []): Product
{
    static $odooProductId = 3000;

    return Product::create(array_merge([
        'odoo_id' => $odooProductId++,
        'name' => 'Product '.uniqid(),
    ], $attributes));
}

function makeLandingPrice(array $attributes = []): LandingPrice
{
    return LandingPrice::create(array_merge([
        'name' => 'Landing Price '.uniqid(),
    ], $attributes));
}

function makeLine(SalesOrder $salesOrder, array $attributes = []): SalesOrderLine
{
    static $odooLineId = 5000;

    return SalesOrderLine::create(array_merge([
        'odoo_id' => $odooLineId++,
        'sales_order_id' => $salesOrder->id,
        'odoo_order_id' => $salesOrder->odoo_id,
        'product_name' => 'Generic Product',
        'price_subtotal' => 0,
    ], $attributes));
}

// --- calculateSellingPrice ---

test('cash payment type uses the full order total as selling price', function () {
    $salesOrder = makeSalesOrder(['amount_total' => 1000, 'x_studio_payment_type' => 'cash']);

    expect(makeCalculator()->calculateSellingPrice($salesOrder))->toBe(1000.0);
});

test('cash or online payment type is treated the same as cash', function () {
    $salesOrder = makeSalesOrder(['amount_total' => 1000, 'x_studio_payment_type' => 'Cash or Online Payment']);

    expect(makeCalculator()->calculateSellingPrice($salesOrder))->toBe(1000.0);
});

test('non-cash payment types apply a 10% discount to the order total', function () {
    $salesOrder = makeSalesOrder(['amount_total' => 1000, 'x_studio_payment_type' => 'finance']);

    expect(makeCalculator()->calculateSellingPrice($salesOrder))->toBe(900.0);
});

// --- calculateProfit ---

test('profit is selling price minus additional cost minus landing price', function () {
    expect(makeCalculator()->calculateProfit(1000, 200, 300))->toBe(500.0);
});

// --- calculateAdditionalCost ---

test('lines without a landing price are marked up by 10% as additional cost', function () {
    $salesOrder = makeSalesOrder();
    makeLine($salesOrder, ['tax_exclusive_amount' => 100, 'product_name' => 'Random Part']);
    $salesOrder->load('lines.product.landingPrices', 'lines.landingPrice');

    expect(makeCalculator()->calculateAdditionalCost($salesOrder))->toEqualWithDelta(110.0, 0.0001);
});

test('installation service lines are excluded from additional cost', function () {
    $salesOrder = makeSalesOrder();
    makeLine($salesOrder, ['tax_exclusive_amount' => 100, 'product_name' => 'Installation Service']);
    $salesOrder->load('lines.product.landingPrices', 'lines.landingPrice');

    expect(makeCalculator()->calculateAdditionalCost($salesOrder))->toBe(0.0);
});

test('supply only lines are excluded from additional cost', function () {
    $salesOrder = makeSalesOrder();
    makeLine($salesOrder, ['tax_exclusive_amount' => 100, 'is_supply_only' => true]);
    $salesOrder->load('lines.product.landingPrices', 'lines.landingPrice');

    expect(makeCalculator()->calculateAdditionalCost($salesOrder))->toBe(0.0);
});

test('lines with a matching landing price are excluded from additional cost', function () {
    $product = makeProduct(['name' => 'Filter Unit']);
    makeLandingPrice([
        'product_id' => $product->id,
        'installation_service' => 250,
        'supply_only' => 200,
    ]);

    $salesOrder = makeSalesOrder();
    makeLine($salesOrder, ['product_id' => $product->id, 'tax_exclusive_amount' => 999]);
    $salesOrder->load('lines.product.landingPrices', 'lines.landingPrice');

    expect(makeCalculator()->calculateAdditionalCost($salesOrder))->toBe(0.0);
});

// --- calculateLandingPrice ---

test('landing price uses installation service cost when order has no supply-only line', function () {
    $product = makeProduct(['name' => 'Filter Unit']);
    makeLandingPrice([
        'product_id' => $product->id,
        'installation_service' => 250,
        'supply_only' => 200,
    ]);

    $salesOrder = makeSalesOrder();
    makeLine($salesOrder, ['product_id' => $product->id]);
    $salesOrder->load('lines.product.landingPrices', 'lines.landingPrice');

    expect(makeCalculator()->calculateLandingPrice($salesOrder))->toBe(250.0);
});

test('landing price uses supply-only cost when order contains a supply-only line', function () {
    $product = makeProduct(['name' => 'Filter Unit']);
    makeLandingPrice([
        'product_id' => $product->id,
        'installation_service' => 250,
        'supply_only' => 200,
    ]);

    $salesOrder = makeSalesOrder();
    makeLine($salesOrder, ['product_id' => $product->id, 'is_supply_only' => true]);
    $salesOrder->load('lines.product.landingPrices', 'lines.landingPrice');

    expect(makeCalculator()->calculateLandingPrice($salesOrder))->toBe(200.0);
});

// --- calculateCommission: end-to-end ---

test('calculateCommission resolves the salesperson via the contact owning the order partner', function () {
    $user = User::factory()->create(['self_gen_base' => 1000, 'company_lead_base' => 500, 'commission_split' => 50]);
    $contact = Contact::create(['odoo_id' => 55, 'display_name' => 'Jane Customer', 'user_id' => $user->id]);
    $salesOrder = makeSalesOrder(['partner_id' => $contact->odoo_id, 'x_studio_sales_source' => 'company_lead']);

    $calculation = makeCalculator()->calculateCommission($salesOrder);

    expect($calculation)->not->toBeNull()
        ->and($calculation->user_id)->toBe($user->id)
        ->and((float) $calculation->base_commission)->toBe(500.0);
});

test('calculateCommission returns null and skips when no salesperson can be resolved', function () {
    $salesOrder = makeSalesOrder();

    expect(makeCalculator()->calculateCommission($salesOrder))->toBeNull();

    $salesOrder->refresh();
    expect($salesOrder->has_commission_calculation)->toBeFalse();
});

test('calculateCommission gives a fixed $200 commission for the special product regardless of source', function () {
    $user = User::factory()->create(['self_gen_base' => 1000, 'company_lead_base' => 500, 'commission_split' => 50]);
    $contact = Contact::create(['odoo_id' => 77, 'display_name' => 'Jane Customer', 'user_id' => $user->id]);
    $salesOrder = makeSalesOrder(['partner_id' => $contact->odoo_id, 'amount_total' => 200]);
    makeLine($salesOrder, ['product_name' => 'Special usro-6s1-2w Widget']);

    $calculation = makeCalculator()->calculateCommission($salesOrder);

    expect((float) $calculation->base_commission)->toBe(200.0)
        ->and($calculation->is_special_product)->toBeTrue();
});

test('calculateCommission applies self-gen base commission for self-generated sales', function () {
    $user = User::factory()->create(['self_gen_base' => 1000, 'company_lead_base' => 500, 'commission_split' => 50]);
    $contact = Contact::create(['odoo_id' => 88, 'display_name' => 'Jane Customer', 'user_id' => $user->id]);
    $salesOrder = makeSalesOrder(['partner_id' => $contact->odoo_id, 'x_studio_sales_source' => 'Self Generated']);

    $calculation = makeCalculator()->calculateCommission($salesOrder);

    expect((float) $calculation->base_commission)->toBe(1000.0)
        ->and($calculation->sales_source)->toBe('self_gen');
});

test('calculateCommission adds a positive profit split to the base commission', function () {
    $user = User::factory()->create(['self_gen_base' => 1000, 'company_lead_base' => 500, 'commission_split' => 50]);
    $contact = Contact::create(['odoo_id' => 99, 'display_name' => 'Jane Customer', 'user_id' => $user->id]);
    // Cash payment, no lines -> additional cost 0, landing price 0, profit = amount_total = 1000
    $salesOrder = makeSalesOrder(['partner_id' => $contact->odoo_id, 'amount_total' => 1000]);

    $calculation = makeCalculator()->calculateCommission($salesOrder);

    expect((float) $calculation->profit)->toBe(1000.0)
        ->and((float) $calculation->extra_commission)->toBe(500.0) // 50% split
        ->and((float) $calculation->final_commission)->toBe(1000.0); // 500 base + 500 extra
});

test('calculateCommission applies negative profit as a full penalty on extra commission', function () {
    $user = User::factory()->create(['self_gen_base' => 1000, 'company_lead_base' => 500, 'commission_split' => 50]);
    $contact = Contact::create(['odoo_id' => 111, 'display_name' => 'Jane Customer', 'user_id' => $user->id]);
    $product = makeProduct(['name' => 'Expensive Unit']);
    makeLandingPrice([
        'product_id' => $product->id,
        'installation_service' => 5000,
        'supply_only' => 5000,
    ]);
    $salesOrder = makeSalesOrder(['partner_id' => $contact->odoo_id, 'amount_total' => 1000]);
    makeLine($salesOrder, ['product_id' => $product->id]);

    $calculation = makeCalculator()->calculateCommission($salesOrder);

    // profit = 1000 (selling price) - 0 (additional cost) - 5000 (landing price) = -4000
    expect((float) $calculation->profit)->toBe(-4000.0)
        ->and((float) $calculation->extra_commission)->toBe(-4000.0);
});

test('calculateCommission preserves manual adjustment and status when recalculated', function () {
    $user = User::factory()->create(['self_gen_base' => 1000, 'company_lead_base' => 500, 'commission_split' => 50]);
    $contact = Contact::create(['odoo_id' => 222, 'display_name' => 'Jane Customer', 'user_id' => $user->id]);
    $salesOrder = makeSalesOrder(['partner_id' => $contact->odoo_id]);

    $calculator = makeCalculator();
    $calculation = $calculator->calculateCommission($salesOrder);
    $calculator->applyManualAdjustment($calculation, 42.00, $user->id, 'Bonus');

    $recalculated = $calculator->recalculate($calculation->fresh());

    expect((float) $recalculated->manual_adjustment)->toBe(42.00)
        ->and($recalculated->status)->toBe('pending');
});
