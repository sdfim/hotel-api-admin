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
        # Expedia Content download archive, unzip, parse json, write to DB
        $schedule->command('download-expedia-data content 12345')->cron('0 1 * * *');
        $schedule->command('download-giata-data')->daily()->at('05:00');
        //TODO: temporal, remove this lineß
        $schedule->command('download-giata-data')->at('15:35');

        $schedule->command('purge-baskets')->cron('0 1 * * *');
        $schedule->command('purge-inspectors')->cron('0 1 * * *');
        $schedule->command('purge-pricing-rules')->cron('0 1 * * *');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
