<?php

namespace Modules\API\ContentRepositoryAPI\routes;

use Illuminate\Support\Facades\Route;
use Modules\Addons\API\Controllers\InformativeServiceController;

class InformativeServicesApiRoutes
{
    public static function routes(): void
    {
        Route::middleware('auth:sanctum')->prefix('services')->group(function () {
            Route::post('/attach', [InformativeServiceController::class, 'attach'])->name('attachService');
            Route::post('/detach', [InformativeServiceController::class, 'detach'])->name('detachService');
            Route::get('/retrieve', [InformativeServiceController::class, 'retrieve'])->name('retrieveService');
        });
    }
}
