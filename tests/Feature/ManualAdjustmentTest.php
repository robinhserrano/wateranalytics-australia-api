<?php

use App\Models\SalesOrder;
use App\Models\User;
use App\Models\CommissionCalculation;
use App\Services\CommissionCalculator;
use App\Services\LegacySyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createCalculation($data = []) {
    return CommissionCalculation::create(array_merge([
        'sales_source' => 'self_gen',
        'payment_type' => 'cash',
        'selling_price' => 1000,
        'profit' => 200,
        'base_commission' => 100,
        'extra_commission' => 50,
        'manual_adjustment' => 0,
        'final_commission' => 150,
        'status' => 'pending',
    ], $data));
}

test('commission calculator applies manual adjustment and creates history', function () {
    $user = User::factory()->create();
    $salesOrder = SalesOrder::create(['name' => 'SO001', 'amount_total' => 1000, 'odoo_id' => 1]);
    
    $calculator = new CommissionCalculator();
    $calculation = createCalculation([
        'sales_order_id' => $salesOrder->id,
        'user_id' => $user->id,
    ]);

    $calculator->applyManualAdjustment($calculation, 20.00, $user->id, 'Test adjustment');

    $calculation->refresh();
    expect((float)$calculation->manual_adjustment)->toBe(20.00);
    expect((float)$calculation->final_commission)->toBe(170.00);
    
    $this->assertDatabaseHas('commission_adjustments', [
        'commission_calculation_id' => $calculation->id,
        'adjustment_amount' => 20.00,
        'reason' => 'Test adjustment (Adjusted total to 20)',
        'adjusted_by' => $user->id,
    ]);
});

test('legacy sync service correctly maps legacy additional deduction and notes', function () {
    $user = User::factory()->create(['legacy_id' => 101]);
    $salesOrder = SalesOrder::create([
        'name' => 'SO002', 
        'amount_total' => 1000,
        'odoo_id' => 2,
        'legacy_additional_deduction' => 50.00,
        'legacy_manual_notes' => 'Legacy Note',
        'legacy_last_manual_add_by' => 101
    ]);
    
    $calculation = createCalculation([
        'sales_order_id' => $salesOrder->id,
        'user_id' => $user->id,
    ]);

    $syncService = new LegacySyncService();
    $syncService->syncLegacyStatus($salesOrder, [101 => $user->id]);

    $calculation->refresh();
    expect((float)$calculation->manual_adjustment)->toBe(50.00);
    
    $this->assertDatabaseHas('commission_adjustments', [
        'commission_calculation_id' => $calculation->id,
        'adjustment_amount' => 50.00,
        'reason' => 'Legacy Note (Adjusted total to 50)',
        'adjusted_by' => $user->id,
    ]);
});

test('legacy sync auto-approves a pending commission on manager confirmation and records history', function () {
    $manager = User::factory()->create(['legacy_id' => 201]);
    $salesOrder = SalesOrder::create([
        'name' => 'SO003',
        'amount_total' => 1000,
        'odoo_id' => 3,
        'legacy_confirmed_by_manager' => true,
        'legacy_last_confirmed_by' => 201,
    ]);
    $calculation = createCalculation([
        'sales_order_id' => $salesOrder->id,
        'user_id' => $manager->id,
        'status' => 'pending',
    ]);

    (new LegacySyncService())->syncLegacyStatus($salesOrder, [201 => $manager->id]);

    $calculation->refresh();
    expect($calculation->status)->toBe('approved')
        ->and($calculation->confirmed_by_manager)->toBeTrue()
        ->and($calculation->approved_by)->toBe($manager->id);

    $this->assertDatabaseHas('commission_approvals', [
        'commission_calculation_id' => $calculation->id,
        'approver_id' => $manager->id,
        'action' => 'confirmed',
    ]);
    $this->assertDatabaseHas('commission_approvals', [
        'commission_calculation_id' => $calculation->id,
        'approver_id' => $manager->id,
        'action' => 'approved',
    ]);
});

test('legacy sync resolves the approver from the database when missing from the in-memory map', function () {
    $manager = User::factory()->create(['legacy_id' => 202]);
    $salesOrder = SalesOrder::create([
        'name' => 'SO004',
        'amount_total' => 1000,
        'odoo_id' => 4,
        'legacy_confirmed_by_manager' => true,
        'legacy_last_confirmed_by' => 202,
    ]);
    $calculation = createCalculation([
        'sales_order_id' => $salesOrder->id,
        'user_id' => $manager->id,
        'status' => 'pending',
    ]);

    // No entry for 202 in the map -> falls back to User::where('legacy_id', ...)
    (new LegacySyncService())->syncLegacyStatus($salesOrder, []);

    expect($calculation->fresh()->approved_by)->toBe($manager->id);
});

test('legacy sync does not duplicate approval history when run twice', function () {
    $manager = User::factory()->create(['legacy_id' => 203]);
    $salesOrder = SalesOrder::create([
        'name' => 'SO005',
        'amount_total' => 1000,
        'odoo_id' => 5,
        'legacy_confirmed_by_manager' => true,
        'legacy_last_confirmed_by' => 203,
    ]);
    $calculation = createCalculation([
        'sales_order_id' => $salesOrder->id,
        'user_id' => $manager->id,
        'status' => 'pending',
    ]);

    $service = new LegacySyncService();
    $service->syncLegacyStatus($salesOrder, [203 => $manager->id]);
    $service->syncLegacyStatus($salesOrder, [203 => $manager->id]);

    $this->assertDatabaseCount('commission_approvals', 2); // one 'confirmed', one 'approved'
});

test('legacy sync marks a commission entered to odoo and records history', function () {
    $officer = User::factory()->create(['legacy_id' => 301]);
    $salesOrder = SalesOrder::create([
        'name' => 'SO006',
        'amount_total' => 1000,
        'odoo_id' => 6,
        'legacy_is_entered_odoo' => true,
        'legacy_last_entered_odoo_by' => 301,
    ]);
    $calculation = createCalculation([
        'sales_order_id' => $salesOrder->id,
        'user_id' => $officer->id,
    ]);

    (new LegacySyncService())->syncLegacyStatus($salesOrder, [301 => $officer->id]);

    expect($calculation->fresh()->entered_to_odoo)->toBeTrue();
    $this->assertDatabaseHas('commission_approvals', [
        'commission_calculation_id' => $calculation->id,
        'approver_id' => $officer->id,
        'action' => 'entered_to_odoo',
    ]);
});
