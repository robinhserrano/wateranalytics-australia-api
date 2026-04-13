<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('odoo:sync-all')
    ->everyMinute()
    ->withoutOverlapping()
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