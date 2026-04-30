<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Delete rejected submissions that are older than 7 days
// Run daily at 2:00 AM
Schedule::command('submissions:delete-rejected')
    ->dailyAt('02:00');

// Reset daily review quota at midnight
Schedule::command('quota:reset')
    ->dailyAt('00:00');

