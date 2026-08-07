<?php

use App\Jobs\CalculateMissingCommissionsJob;
use App\Jobs\OdooSyncStepJob;
use App\Jobs\SyncOdooContactsJob;
use App\Jobs\SyncOdooInstallationDatesJob;
use App\Jobs\SyncOdooProductsJob;
use App\Jobs\SyncOdooSalesOrdersJob;
use App\Jobs\SyncOdooStocksJob;
use App\Services\SyncLogger;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Dispatches the Odoo sync pipeline as a chain of queued jobs (processed by
// Horizon on the 'odoo-sync' queue) instead of running it synchronously in
// the scheduler process.
//
// Cache::add() is the actual overlap guard here: it atomically sets the lock
// key only if absent, so only one scheduler tick at a time can win it. This
// is NOT ShouldBeUnique on the first job - Bus::chain(...)->dispatch() does
// not honor ShouldBeUnique at all (Laravel only checks it in PendingDispatch,
// i.e. plain Job::dispatch()), so that would silently do nothing. The lock
// is released as soon as the chain finishes (success: CalculateMissing-
// CommissionsJob; failure: the ->catch() below) and self-heals via its own
// 900s TTL if a run dies without triggering either (e.g. a killed worker).
Schedule::call(function () {
    if (! Cache::add(OdooSyncStepJob::PIPELINE_LOCK_KEY, true, 900)) {
        return;
    }

    $log = app(SyncLogger::class)->start('odoo:sync-all');

    Bus::chain([
        new SyncOdooContactsJob($log->id),
        new SyncOdooProductsJob($log->id),
        new SyncOdooStocksJob($log->id),
        new SyncOdooSalesOrdersJob($log->id),
        new SyncOdooInstallationDatesJob($log->id),
        new CalculateMissingCommissionsJob($log->id),
    ])
        ->onQueue('odoo-sync')
        ->catch(function (Throwable $e) use ($log) {
            app(SyncLogger::class)->fail($log, $e);
            Cache::forget(OdooSyncStepJob::PIPELINE_LOCK_KEY);
        })
        ->dispatch();
})
    ->everyMinute()
    ->name('odoo-sync-dispatch')
    ->withoutOverlapping(15)
    ->onOneServer();

// Daily Backups: Database & Files to S3 at 1:00 AM
Schedule::command('backup:run')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->onOneServer();

// Cleanup old backups (retention policy) at 1:30 AM
Schedule::command('backup:clean')
    ->dailyAt('01:30')
    ->onOneServer();
