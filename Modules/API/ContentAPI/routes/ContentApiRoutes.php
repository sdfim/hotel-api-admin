<?php

namespace Modules\API\ContentAPI\routes;

use Illuminate\Support\Facades\Route;
use Modules\API\Controllers\ApiHandlers\DestinationsController;
use Modules\API\Controllers\RouteApiController;

class ContentApiRoutes
{
    /**
     * @return void
     */
    public static function routes(): void
    {
        Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'content'], function () {
            Route::post('/search', [RouteApiController::class, 'handle'])->name('search');
            Route::get('/detail', [RouteApiController::class, 'handle'])->name('detail');
            Route::get('/destinations', [DestinationsController::class, 'destinations'])->name('destinations');
        });
    }
}
