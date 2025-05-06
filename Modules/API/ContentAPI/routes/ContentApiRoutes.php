<?php

namespace Modules\API\ContentAPI\routes;

use Illuminate\Support\Facades\Route;
use Modules\API\Controllers\ApiHandlers\DestinationsController;
use Modules\API\Controllers\RouteApiController;

class ContentApiRoutes
{
    public static function routes(): void
    {
        Route::middleware('auth:sanctum')->prefix('content')->group(function () {
            Route::post('/search', [RouteApiController::class, 'handle'])->name('search');
            Route::get('/detail', [RouteApiController::class, 'handle'])->name('detail');
            Route::get('/destinations', [DestinationsController::class, 'destinations'])->name('destinations');
        });

        Route::middleware('auth:sanctum')->prefix('v1/content')->group(function () {
            Route::post('/search', [RouteApiController::class, 'handle'])->name('v1.search');
            Route::get('/detail', [RouteApiController::class, 'handle'])->name('v1.detail');
            Route::get('/destinations', [DestinationsController::class, 'destinations'])->name('v1.destinations');
        });
    }
}
