<?php

use App\Models\CommissionCalculation;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SyncLog;
use App\Models\User;
use Carbon\CarbonImmutable;

// Covers /api/ops-brief - the read-only aggregates n8n turns into the morning/afternoon Discord brief.

test('ops brief rejects a missing or wrong token and is off when unset', function () {
    config(['services.sync_health.token' => 'secret']);
    $this->getJson('/api/ops-brief')->assertUnauthorized();
    $this->withToken('wrong')->getJson('/api/ops-brief')->assertUnauthorized();

    config(['services.sync_health.token' => null]);
    $this->withToken('')->getJson('/api/ops-brief')->assertUnauthorized();
});

test('ops brief returns order, commission, reconcile and data-quality aggregates', function () {
    config(['services.sync_health.token' => 'secret']);
    // Wednesday 10:00 in Sydney (AEDT, UTC+11) = Tuesday 23:00 UTC
    $this->travelTo(CarbonImmutable::parse('2026-10-21 10:00', 'Australia/Sydney'));

    $order = fn (string $sydney, float $total, array $attrs = []) => SalesOrder::create(array_merge([
        'odoo_id' => random_int(1, 1_000_000), 'name' => 'S', 'state' => 'sale', 'amount_total' => $total,
        'create_date' => CarbonImmutable::parse($sydney, 'Australia/Sydney')->utc()->toDateTimeString(),
    ], $attrs));
    $order('2026-10-21 08:00', 100);                                  // today (still the 20th in UTC)
    $order('2026-10-21 09:00', 999, ['state' => 'cancel']);           // not counted: cancelled
    $order('2026-10-21 09:00', 999, ['odoo_missing_at' => now()]);    // not counted: deleted in Odoo
    $order('2026-10-20 23:30', 250);                                  // yesterday
    $order('2026-10-20 23:59', 50);                                   // yesterday
    $order('2026-10-14 12:00', 300);                                  // 1 week ago
    $order('2026-09-30 00:10', 500);                                  // 3 weeks ago (AEST, before DST)
    $order('2026-09-29 23:50', 999);                                  // a Tuesday, not counted

    $user = User::factory()->create();
    $commission = fn (string $status, array $attrs = []) => CommissionCalculation::create(array_merge([
        'sales_order_id' => SalesOrder::create(['odoo_id' => random_int(1_000_001, 2_000_000), 'name' => 'C', 'state' => 'draft'])->id,
        'user_id' => $user->id, 'sales_source' => 'company_lead', 'payment_type' => 'cash', 'selling_price' => 1, 'profit' => 1,
        'base_commission' => 1, 'extra_commission' => 0, 'final_commission' => 1, 'status' => $status,
    ], $attrs));
    $commission('pending');
    $commission('pending');
    $commission('rejected', ['rejected_at' => now()->subHours(2), 'rejection_reason' => CommissionCalculation::AUTO_REJECT_REASON]);
    $commission('rejected', ['rejected_at' => now()->subDays(2), 'rejection_reason' => CommissionCalculation::AUTO_REJECT_REASON]);
    $commission('rejected', ['rejected_at' => now()->subHour(), 'rejected_by' => $user->id, 'rejection_reason' => 'manual']);

    SyncLog::create(['command' => 'odoo:reconcile-sales', 'status' => 'failed', 'started_at' => now()->subDay(), 'message' => 'old']);
    SyncLog::create(['command' => 'odoo:reconcile-sales', 'status' => 'completed', 'started_at' => now(), 'completed_at' => now(), 'message' => 'Checked 3']);
    SyncLog::create(['command' => 'odoo:sync-all', 'status' => 'completed', 'started_at' => now()]);

    Product::create(['odoo_id' => 1, 'name' => 'Filter', 'categ_name' => 'Filters']);
    Product::create(['odoo_id' => 2, 'name' => 'Mystery']);

    $this->withToken('secret')->getJson('/api/ops-brief')
        ->assertOk()
        ->assertJson([
            'timezone' => 'Australia/Sydney',
            'orders' => [
                'today' => ['date' => '2026-10-21', 'count' => 1, 'revenue' => 100],
                'yesterday' => ['date' => '2026-10-20', 'count' => 2, 'revenue' => 300],
                'weekday_avg_4w' => ['weekday' => 'Wednesday', 'count' => 0.5, 'revenue' => 200],
            ],
            'commissions' => ['pending' => 2, 'auto_rejected_24h' => 1],
            'last_reconcile' => ['status' => 'completed', 'message' => 'Checked 3'],
            'data_quality' => ['products_without_category' => 1],
        ])
        ->assertJsonMissingPath('last_reconcile.id');
});
