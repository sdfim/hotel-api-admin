<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Modules\API\BookingAPI\routes\BookingApiRoutes;
use Modules\API\ContentAPI\routes\ContentApiRoutes;
use Modules\API\ContentRepositoryAPI\routes\ContentRepositoryApiRoutes;
use Modules\API\PricingAPI\routes\PricingApiRoutes;
use Modules\API\Report\routes\ReportApiRoutes;

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
            */
        });
    }

    protected function contentApiRoutes()
    {
        Route::domain(config('domains.app_content_domain'))
            ->group(function () {
                ContentApiRoutes::routes();
            });
    }

    protected function pricingApiRoutes()
    {
        Route::domain(config('domains.app_pricing_domain'))
            ->group(function () {
                PricingApiRoutes::routes();
            });
    }

    protected function bookingApiRoutes()
    {
        Route::domain(config('domains.app_booking_domain'))
            ->group(function () {
                BookingApiRoutes::routes();
            });
    }

    protected function reportApiRoutes()
    {
        Route::domain(config('domains.app_report_domain'))
            ->group(function () {
                ReportApiRoutes::routes();
            });
    }

    protected function hotelContentRepositoryApiRoutes()
    {
        Route::domain(config('domains.app_repository_domain'))
            ->group(function () {
                ContentRepositoryApiRoutes::routes();
            });
    }
}
