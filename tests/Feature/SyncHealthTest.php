<?php

use App\Models\SalesOrder;
use App\Models\SyncLog;

// Covers /api/sync-health - the read-only endpoint the n8n monitor polls to check the Odoo sync.

test('sync health rejects a missing or wrong token and is off when unset', function () {
    config(['services.sync_health.token' => 'secret']);
    $this->getJson('/api/sync-health')->assertUnauthorized();
    $this->withToken('wrong')->getJson('/api/sync-health')->assertUnauthorized();

    config(['services.sync_health.token' => null]);
    $this->withToken('')->getJson('/api/sync-health')->assertUnauthorized();
});

test('sync health returns latest runs, counts and only the requested records', function () {
    config(['services.sync_health.token' => 'secret']);
    SyncLog::create(['command' => 'odoo:sync-all', 'status' => 'completed', 'started_at' => now()]);
    SalesOrder::create(['odoo_id' => 7, 'name' => 'S00007', 'state' => 'sale', 'amount_total' => 100]);
    SalesOrder::create(['odoo_id' => 8, 'name' => 'S00008']);

    $this->withToken('secret')->getJson('/api/sync-health?ids=7,999')
        ->assertOk()
        ->assertJsonPath('runs.0.status', 'completed')
        ->assertJsonPath('counts.sale_orders', 2)
        ->assertJsonCount(1, 'records')
        ->assertJsonPath('records.0.odoo_id', 7);
});
