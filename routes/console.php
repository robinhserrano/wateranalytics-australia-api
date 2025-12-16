<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('odoo:sync-all')
             // 1. Run the check every minute
             ->everyMinute() 
             
             // 2. Only run the command if a previous instance is NOT running
             ->withoutOverlapping() 
             
             // 3. Recommended: Use this if you have multiple servers
             ->onOneServer(); 