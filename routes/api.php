<?php

use Modules\API\BookingAPI\routes\BookingApiRoutes;
use Modules\API\ContentAPI\routes\ContentApiRoutes;
use Modules\API\PricingAPI\routes\PricingApiRoutes;

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

