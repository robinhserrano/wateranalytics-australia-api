<?php

use App\Models\SyncLog;

// Incremental syncs fetch write_date > watermark. Using the last run's completed_at skipped every Odoo
// edit made while that run was in progress; the watermark must be its started_at (minus skew margin).

test('watermark is the last successful run start minus a minute, not its completion', function () {
    SyncLog::create(['command' => 'odoo:sync-contacts', 'status' => 'completed',
        'started_at' => '2026-10-14 10:00:00', 'completed_at' => '2026-10-14 10:00:30']);

    expect(SyncLog::watermarkFor('odoo:sync-contacts')->toDateTimeString())->toBe('2026-10-14 09:59:00');
});

test('watermark ignores failed and running runs and other commands', function () {
    SyncLog::create(['command' => 'odoo:sync-contacts', 'status' => 'completed',
        'started_at' => '2026-10-14 10:00:00', 'completed_at' => '2026-10-14 10:00:30']);
    SyncLog::create(['command' => 'odoo:sync-contacts', 'status' => 'failed',
        'started_at' => '2026-10-14 10:05:00', 'completed_at' => '2026-10-14 10:05:10']);
    SyncLog::create(['command' => 'odoo:sync-contacts', 'status' => 'running', 'started_at' => '2026-10-14 10:10:00']);
    SyncLog::create(['command' => 'odoo:sync-products', 'status' => 'completed',
        'started_at' => '2026-10-14 11:00:00', 'completed_at' => '2026-10-14 11:00:30']);

    expect(SyncLog::watermarkFor('odoo:sync-contacts')->toDateTimeString())->toBe('2026-10-14 09:59:00');
});

test('watermark is null before the first successful run, and honours a custom success status', function () {
    expect(SyncLog::watermarkFor('odoo:sync-contacts'))->toBeNull();

    SyncLog::create(['command' => 'odoo:sync-installation-dates', 'status' => 'success',
        'started_at' => '2026-10-14 10:00:00', 'completed_at' => '2026-10-14 10:02:00']);

    expect(SyncLog::watermarkFor('odoo:sync-installation-dates', 'success')->toDateTimeString())->toBe('2026-10-14 09:59:00')
        ->and(SyncLog::watermarkFor('odoo:sync-installation-dates', ['completed', 'success']))->not->toBeNull()
        ->and(SyncLog::watermarkFor('odoo:sync-installation-dates'))->toBeNull();
});
