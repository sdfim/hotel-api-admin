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

// Expedia Content download archive, unzip, parse json, write to DB
Schedule::command('download-expedia-data content 12345')->cron('0 1 * * *');
Schedule::command('hilton:fetch-properties', ['--limit' => 50])->cron('0 1 * * *');

// Schedule::command('download-giata-data')->daily()->at('05:00');

Schedule::command('purge-baskets')->cron('0 1 * * *');
Schedule::command('purge-inspectors')->cron('0 1 * * *');
Schedule::command('purge-pricing-rules')->cron('0 1 * * *');
