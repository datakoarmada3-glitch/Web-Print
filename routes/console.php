<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Monitor print job statuses from CUPS every minute
Schedule::command('print:monitor-status')->everyMinute();

// Sync printer status every 5 minutes
Schedule::command('print:sync-printer-status')->everyFiveMinutes();

// Cleanup old print files daily at 2 AM
Schedule::command('print:cleanup-files')->dailyAt('02:00');
