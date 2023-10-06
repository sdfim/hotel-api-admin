<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Modules\API\Controllers\ExpediaHotelApiHandler;
use Modules\API\Suppliers\ExpediaSupplier\ExperiaService;
use Modules\API\Suppliers\ExpediaSupplier\PropertyCallFactory;
use Modules\API\Suppliers\ExpediaSupplier\RapidClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register (): void
    {
        $this->app->singleton(RapidClient::class, function ($app) {
            $apiKey = env('EXPEDIA_RAPID_API_KEY');
            $sharedSecret = env('EXPEDIA_RAPID_SHARED_SECRET');
            return new RapidClient($apiKey, $sharedSecret);
        });

        $this->app->singleton(PropertyCallFactory::class, function ($app) {
            $rapidClient = $app->make(RapidClient::class);
            return new PropertyCallFactory($rapidClient);
        });

        $this->app->singleton(ExperiaService::class, function ($app) {
            $propertyCallFactory = $app->make(PropertyCallFactory::class);
            return new ExperiaService($propertyCallFactory);
        });

        $this->app->singleton(RoteApiController::class, function ($app) {
            $experiaService = $app->make(ExperiaService::class);
            return new ExpediaHotelApiHandler($experiaService);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot (): void
    {
        $currentUrl = \Illuminate\Support\Facades\URL::current();
        if (!str_contains($currentUrl, 'localhost') && !str_contains($currentUrl, '127.0.0.1')) {
            \URL::forceScheme('https');
        }
        Schema::defaultStringLength(191);
    }
}
