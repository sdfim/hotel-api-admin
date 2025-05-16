<?php

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

/**
 * REMOVE THIS COMMAND WHEN MULTI ROOM IS PROPERLY INTEGRATED
 */
Artisan::command('obe {scenario} {action}', function () {
    $scenario = $this->argument('scenario');
    $action = $this->argument('action');

    $client = new \Modules\API\Suppliers\HbsiSupplier\MultiRoomTesting();

    $this->info($client->execute($scenario, $action));
});

// Download Content Suppliers Data into the DB
// Expedia Content download archive, unzip, parse json, write to DB
Schedule::command('download-expedia-data content 12345')->cron('0 1 * * *');
// Hilton Content download to DB
// Schedule::command('hilton:fetch-properties', ['--limit' => 50])->weeklyOn(6, '05:00');
// Download IcePortal data to DB
Schedule::command('download-iceportal-data')->weeklyOn(6, '06:00');

// Download and process Giata data including Mapping Expedia, HBSI, IcePortal
Schedule::command('download-giata-data')->weeklyOn(7, '05:00');

Schedule::command('purge-baskets')->cron('0 1 * * *');
Schedule::command('purge-inspectors')->cron('0 1 * * *');
Schedule::command('purge-pricing-rules')->cron('0 1 * * *');
Schedule::command('purge-booking-item-cache')->cron('0 * * * *');
