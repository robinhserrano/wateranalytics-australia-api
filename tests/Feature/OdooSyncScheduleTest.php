<?php

use App\Jobs\SyncOdooContactsJob;
use App\Jobs\SyncOdooInstallationDatesJob;
use App\Jobs\SyncOdooProductsJob;
use App\Jobs\SyncOdooSalesOrdersJob;
use App\Jobs\SyncOdooStocksJob;
use App\Models\SyncLog;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Bus;

test('scheduled odoo sync dispatches a chained job pipeline instead of running synchronously', function () {
    Bus::fake();

    $event = collect(app(Schedule::class)->events())
        ->first(fn ($event) => $event->description === 'odoo-sync-dispatch');

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
