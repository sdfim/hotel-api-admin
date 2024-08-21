<?php

namespace Modules\API\Report\routes;

use Illuminate\Support\Facades\Route;
use Modules\API\BookingAPI\BookingApiHandlers\BookApiHandler;
use Modules\API\Controllers\RouteBookingApiController;
use Modules\API\Controllers\RouteReportApiController;

class ReportApiRoutes
{
    public static function routes(): void
    {
        Route::middleware('auth:sanctum')->prefix('reports')->group(function () {
            Route::get('/bookings', [RouteReportApiController::class, 'handle'])->name('bookings');
        });
    }
}
