<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Modules\API\Suppliers\ExpediaSupplier\ExpediaService;
use Modules\API\Suppliers\ExpediaSupplier\PropertyCallFactory;
use Modules\API\Suppliers\ExpediaSupplier\RapidClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(RapidClient::class, function () {
            return new RapidClient();
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
    }
}
