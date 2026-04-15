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
