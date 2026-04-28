<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Delete rejected submissions that are older than 7 days
        // Run daily at 2:00 AM
        $schedule->command('submissions:delete-rejected')
            ->dailyAt('02:00')
            ->name('delete-rejected-submissions')
            ->description('Delete submissions rejected for 7 days or more');

        // Reset daily review quota at midnight
        $schedule->command('quota:reset')
            ->dailyAt('00:00')
            ->name('reset-daily-quota')
            ->description('Reset daily review quota for all users');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
