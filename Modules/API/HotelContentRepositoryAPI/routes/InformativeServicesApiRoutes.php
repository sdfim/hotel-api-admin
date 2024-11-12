<?php

namespace Modules\API\HotelContentRepositoryAPI\routes;

use Illuminate\Support\Facades\Route;
use Modules\HotelContentRepository\API\Controllers\InformativeServiceController;

class InformativeServicesApiRoutes
{
    public static function routes(): void
    {
        Route::middleware('auth:sanctum')->prefix('repo')->group(function () {
            Route::post('services/attach', [InformativeServiceController::class, 'attach']);
            Route::delete('services/detach', [InformativeServiceController::class, 'detach']);
        });
    }
}
