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
