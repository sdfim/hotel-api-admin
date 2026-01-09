<?php

namespace Modules\API\Suppliers\Oracle;

use Illuminate\Support\ServiceProvider;
use Modules\API\Suppliers\Oracle\Adapters\OracleHotelAdapter;
use Modules\API\Suppliers\Oracle\Adapters\OracleHotelBookingAdapter;

class OracleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->tag(
            OracleHotelAdapter::class,
            'hotel.search.suppliers'
        );

        $this->app->tag(
            OracleHotelBookingAdapter::class,
            'hotel.booking.suppliers'
        );
    }
}
