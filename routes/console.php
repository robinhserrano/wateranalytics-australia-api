<?php

use App\Jobs\SyncOdooContactsJob;
use App\Jobs\SyncOdooProductsJob;
use App\Jobs\SyncOdooSalesOrdersJob;
use App\Jobs\SyncOdooStocksJob;
use App\Services\SyncLogger;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Dispatches the Odoo sync pipeline as a chain of queued jobs (processed by
// Horizon on the 'odoo-sync' queue) instead of running it synchronously in
// the scheduler process. SyncOdooContactsJob is unique across the whole
// pipeline (see its uniqueId()), which is what actually prevents overlap -
// withoutOverlapping()/onOneServer() here only guard this near-instant
// dispatch closure, not the (potentially long) queued run it kicks off.
Schedule::call(function () {
    $log = app(SyncLogger::class)->start('odoo:sync-all');

    Bus::chain([
        new SyncOdooContactsJob($log->id),
        new SyncOdooProductsJob($log->id),
        new SyncOdooStocksJob($log->id),
        new SyncOdooSalesOrdersJob($log->id),
    ])
        ->onQueue('odoo-sync')
        ->catch(fn (Throwable $e) => app(SyncLogger::class)->fail($log, $e))
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
