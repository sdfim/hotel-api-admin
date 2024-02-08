<?php

namespace Modules\API\PricingAPI\routes;

use Illuminate\Support\Facades\Route;
use Modules\API\Controllers\RouteApiController;

class PricingApiRoutes
{
    /**
     * @return void
     */
    public static function routes(): void
    {
        Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'pricing'], function () {
            Route::post('/search', [RouteApiController::class, 'handle'])->name('price');
        });
    }
}
