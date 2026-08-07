<?php

use App\Jobs\OdooSyncStepJob;
use App\Jobs\SyncOdooContactsJob;
use App\Jobs\SyncOdooInstallationDatesJob;
use App\Jobs\SyncOdooProductsJob;
use App\Jobs\SyncOdooSalesOrdersJob;
use App\Jobs\SyncOdooStocksJob;
use App\Models\SyncLog;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;

function getOdooSyncDispatchEvent()
{
    return collect(app(Schedule::class)->events())
        ->first(fn ($event) => $event->description === 'odoo-sync-dispatch');
}

test('scheduled odoo sync dispatches a chained job pipeline instead of running synchronously', function () {
    Bus::fake();

    $event = getOdooSyncDispatchEvent();
    expect($event)->not->toBeNull();

    $event->run(app());

    Bus::assertChained([
        SyncOdooContactsJob::class,
        SyncOdooProductsJob::class,
        SyncOdooStocksJob::class,
        SyncOdooSalesOrdersJob::class,
        SyncOdooInstallationDatesJob::class,
    ]);

    expect(SyncLog::where('command', 'odoo:sync-all')->where('status', 'running')->exists())->toBeTrue();
});

test('a second tick while the pipeline lock is held does not start a new run', function () {
    Bus::fake();

    $event = getOdooSyncDispatchEvent();
    $event->run(app());
    $event->run(app());

    expect(SyncLog::where('command', 'odoo:sync-all')->count())->toBe(1);
});

test('a new tick can start a run again once the pipeline lock is released', function () {
    Bus::fake();

    $event = getOdooSyncDispatchEvent();
    $event->run(app());

    // Simulate the last job of the chain (or the ->catch() failure handler)
    // releasing the lock once the previous run finished.
    Cache::forget(OdooSyncStepJob::PIPELINE_LOCK_KEY);

    $event->run(app());

    expect(SyncLog::where('command', 'odoo:sync-all')->count())->toBe(2);
});
