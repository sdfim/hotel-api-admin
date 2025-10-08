<?php

use App\Services\ScheduledTaskService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
	$this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// Use dynamic scheduling from the database
app(ScheduledTaskService::class)->registerTasks(app(Schedule::class));

// Legacy static schedules - these will be removed once migration to the database is complete
// Uncomment if needed during transition
//// Download Content Suppliers Data into the DB
//// Expedia Content download archive, unzip, parse json, write to DB
//// Schedule::command('download-expedia-data content 12345')->weeklyOn(0, '01:00');
//// Hilton Content download to DB
//// Schedule::command('hilton:fetch-properties', ['--limit' => 50])->weeklyOn(6, '05:00');
//// Download IcePortal data to DB
//// Schedule::command('download-iceportal-data')->weeklyOn(4, '01:00');
//
//// Download and process Giata data including Mapping Expedia, HBSI, IcePortal
//// Schedule::command('download-giata-data')->weeklyOn(4, '04:00');

//Schedule::command('purge-baskets')->cron('0 1 * * *')->timezone('America/New_York');
//Schedule::command('purge-inspectors')->cron('0 1 * * *')->timezone('America/New_York');
//Schedule::command('purge-pricing-rules')->cron('0 1 * * *')->timezone('America/New_York');
//Schedule::command('purge-booking-item-cache')->cron('0 * * * *')->timezone('America/New_York');

// Report unmapped data daily at 7AM EST
Schedule::command('report-unmapped-data')->cron('0 12 * * *')->timezone('America/New_York');
