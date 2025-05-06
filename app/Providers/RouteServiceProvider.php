<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Modules\API\BookingAPI\routes\BookingApiRoutes;
use Modules\API\ContentAPI\routes\ContentApiRoutes;
use Modules\API\ContentRepositoryAPI\routes\ContentRepositoryApiRoutes;
use Modules\API\ContentRepositoryAPI\routes\InformativeServicesApiRoutes;
use Modules\API\PricingAPI\routes\PricingApiRoutes;
use Modules\API\Report\routes\ReportApiRoutes;
use Modules\Insurance\routes\InsuranceApiRoutes;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/admin';

    public function boot()
    {
        $this->routes(function () {
            /*
            $this->contentApiRoutes();
            $this->pricingApiRoutes();
            $this->bookingApiRoutes();
            $this->reportApiRoutes();
            $this->hotelContentRepositoryApiRoutes();
            */
        });
    }

    protected function contentApiRoutes()
    {
        Route::domain(env('APP_CONTENT_DOMAIN'))
            ->group(function () {
                ContentApiRoutes::routes();
            });
    }

    protected function pricingApiRoutes()
    {
        Route::domain(env('APP_PRICING_DOMAIN'))
            ->group(function () {
                PricingApiRoutes::routes();
            });
    }

    protected function bookingApiRoutes()
    {
        Route::domain(env('APP_BOOKING_DOMAIN'))
            ->group(function () {
                BookingApiRoutes::routes();
                InsuranceApiRoutes::routes();
                InformativeServicesApiRoutes::routes();
            });
    }

    protected function reportApiRoutes()
    {
        Route::domain(env('APP_REPORT_DOMAIN'))
            ->group(function () {
                ReportApiRoutes::routes();
            });
    }

    protected function hotelContentRepositoryApiRoutes()
    {
        Route::domain(env('APP_CONTENT_REPOSITORY_DOMAIN'))
            ->group(function () {
                ContentRepositoryApiRoutes::routes();
            });
    }
}
