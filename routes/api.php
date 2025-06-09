<?php

use App\Http\Controllers\DummyHealthController;
use Modules\API\BookingAPI\routes\BookingApiRoutes;
use Modules\API\Channels\routes\ChannelsApiRoutes;
use Modules\API\ContentAPI\routes\ContentApiRoutes;
use Modules\API\PricingAPI\routes\PricingApiRoutes;
use Modules\API\Report\routes\ReportApiRoutes;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

ContentApiRoutes::routes();

PricingApiRoutes::routes();

BookingApiRoutes::routes();

ReportApiRoutes::routes();

ChannelsApiRoutes::routes();
