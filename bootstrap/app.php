<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        \App\Support\Services\Logging\LoggingServiceProvider::class,
        \App\Support\Services\UniversalUniqueIdentifier\UniversalUniqueIdentifierServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // $middleware->redirectUsersTo(config('app.url').AppServiceProvider::HOME);

        $middleware->append([
            \App\Http\Middleware\SetLocationHeader::class,
            \App\Http\Middleware\RewriteUrls::class,
            \App\Http\Middleware\CheckChannelNotDeleted::class,
        ]);

        $middleware->use([
            \App\Support\Services\UniversalUniqueIdentifier\UniversalUniqueIdentifierMiddleware::class,
        ]);

        $middleware->throttleApi();
        $middleware->api(\App\Http\Middleware\FakeDataEndpoints::class);

        $middleware->replace(\Illuminate\Http\Middleware\TrustProxies::class, \App\Http\Middleware\TrustProxies::class);

        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'signed' => \App\Http\Middleware\ValidateSignature::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
