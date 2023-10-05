<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule (Schedule $schedule): void
    {
		# Expedia Content download archive, unzip, parse json, write to DB
		$schedule->command('download-expedia-data content 1')->cron('0 1 * * *');

		# GIATA Get the XML content in streming from the response body, parse XML, write to DB
		$schedule->command('download-giata-data')->cron('0 2 * * *');

		# Mapper Expedia Giata. search for Expedia Giata relationships and write to DB
		$schedule->command('make-mapper-expedia-giate 1 2')->cron('0 3 * * *');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands (): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
