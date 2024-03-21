<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

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
Artisan::command('obe {scenario} {action}', function() {
    $scenario = $this->argument('scenario');
    $action = $this->argument('action');

    $client = new \Modules\API\Suppliers\HbsiSupplier\MultiRoomTesting();

    $this->info($client->execute($scenario, $action));
});
