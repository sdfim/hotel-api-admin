<?php

use Modules\API\BookingAPI\routes\BookingApiRoutes;
use Modules\API\ContentAPI\routes\ContentApiRoutes;
use Modules\API\HotelContentRepositoryAPI\routes\HotelContentRepositoryApiRoutes;
use Modules\API\HotelContentRepositoryAPI\routes\InformativeServicesApiRoutes;
use Modules\API\PricingAPI\routes\PricingApiRoutes;
use Modules\API\Report\routes\ReportApiRoutes;
use Modules\Insurance\routes\InsuranceApiRoutes;

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

HotelContentRepositoryApiRoutes::routes();

InsuranceApiRoutes::routes();

InformativeServicesApiRoutes::routes();
