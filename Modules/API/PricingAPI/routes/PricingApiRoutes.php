<?php

namespace Modules\API\PricingAPI\routes;

use Illuminate\Support\Facades\Route;
use Modules\API\Controllers\RouteApiController;

class PricingApiRoutes
{
    public static function routes(): void
    {
        Route::middleware('auth:sanctum')->prefix('pricing')->group(function () {
            Route::post('/search', [RouteApiController::class, 'handle'])->name('price');
        });
    }
}
