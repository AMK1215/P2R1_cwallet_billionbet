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
        // Transaction logs cleanup every 3 days at 2 AM
        $schedule->command('logs:cleanup-transaction --days=3 --batch-size=1000')
            ->everyThreeDays()
            ->at('02:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/cleanup.log'));

        // Deadlock monitoring every hour
        $schedule->command('deadlock:monitor --once')
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/deadlock-monitoring.log'));

        // Database optimization every week
        $schedule->call(function () {
            \DB::statement('VACUUM ANALYZE transaction_logs');
            \DB::statement('VACUUM ANALYZE deadlock_logs');
        })->weekly()
            ->sundays()
            ->at('03:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/optimization.log'));
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