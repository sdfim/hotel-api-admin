<?php

namespace Modules\Insurance\routes;

use Illuminate\Support\Facades\Route;
use Modules\Insurance\API\Controllers\InsuranceApiController;

class InsuranceApiRoutes
{
    public static function routes(): void
    {
        Route::middleware('auth:sanctum')->prefix('insurance')->group(function () {
            Route::post('add', [InsuranceApiController::class, 'addInsurance']);
        });
    }
}

