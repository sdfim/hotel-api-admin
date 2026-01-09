<?php

namespace Modules\API\Auth\routes;

use Illuminate\Support\Facades\Route;
use Modules\API\Auth\Controllers\ChannelAuthController;
use Modules\API\Auth\Controllers\JwtLoginController;

class AuthApiRoutes
{
    public static function routes(): void
    {
        Route::prefix('auth')
            ->middleware('throttle:10,1')// rate limit: 10 req/min
            ->group(function () {
                // Exchange user email+password -> channel token
                Route::post('/channel-token', [ChannelAuthController::class, 'issueChannelToken']);
                Route::post('/jwt-login', [JwtLoginController::class, 'login']);
                Route::post('/generate-external-jwt', [JwtLoginController::class, 'generateExternalJwt']);
            });
    }
}
