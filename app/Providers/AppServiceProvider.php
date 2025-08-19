<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Modules\API\Suppliers\ExpediaSupplier\ExpediaService;
use Modules\API\Suppliers\ExpediaSupplier\PropertyCallFactory;
use Modules\API\Suppliers\ExpediaSupplier\RapidClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/admin/vendor-repository';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(RapidClient::class, function () {
            return new RapidClient;
        });

        $this->app->singleton(PropertyCallFactory::class, function ($app) {
            $rapidClient = $app->make(RapidClient::class);

            return new PropertyCallFactory($rapidClient);
        });

        $this->app->singleton(ExpediaService::class, function ($app) {
            // TODO: need to review the next two lines, as the constructor of the ExpediaService class does not have any input parameters.
            $propertyCallFactory = $app->make(PropertyCallFactory::class);

            return new ExpediaService($propertyCallFactory);
        });

        $this->app->bind(\Illuminate\Contracts\Debug\ExceptionHandler::class, \App\Exceptions\Handler::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $currentUrl = URL::current();
        if (! str_contains($currentUrl, 'localhost') && ! str_contains($currentUrl, '127.0.0.1')) {
            URL::forceScheme('https');
        }
        Schema::defaultStringLength(191);

        $this->bootRoute();

        // Fill all keys with constant prefix: when the application starts
        $this->cacheAllConstants();
    }

    protected function cacheAllConstants(): void
    {
        $config = \App\Models\GeneralConfiguration::first();
        if ($config) {
            if (! is_null($config->content_supplier)) {
                Cache::forever('constant:content_supplier', $config->content_supplier);
            }
        }
    }

    public function bootRoute(): void
    {
        $disable = config('engine.disable_throttle');

        RateLimiter::for('api', function (Request $request) use ($disable) {
            if ($disable) {
                return Limit::perMinute(1000)->by($request->user()?->id ?: $request->ip());
            }

            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

    }
}
