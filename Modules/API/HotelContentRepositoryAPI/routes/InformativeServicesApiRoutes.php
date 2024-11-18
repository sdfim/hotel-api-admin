<?php

namespace Modules\API\HotelContentRepositoryAPI\routes;

use Illuminate\Support\Facades\Route;
use Modules\Addons\API\Controllers\InformativeServiceController;

class InformativeServicesApiRoutes
{
    public static function routes(): void
    {
        Route::middleware('auth:sanctum')->prefix('services')->group(function () {
            Route::post('/attach', [InformativeServiceController::class, 'attach']);
            Route::post('/detach', [InformativeServiceController::class, 'detach']);
            Route::get('/retrieve', [InformativeServiceController::class, 'retrieve']);
        });
    }
}
