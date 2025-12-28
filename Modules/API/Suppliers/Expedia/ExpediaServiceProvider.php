<?php

namespace Modules\API\Suppliers\Expedia;

use Illuminate\Support\ServiceProvider;
use Modules\API\Suppliers\Expedia\Adapters\ExpediaHotelAdapter;
use Modules\API\Suppliers\Expedia\Adapters\ExpediaHotelBookingAdapter;

class ExpediaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->tag(
            ExpediaHotelAdapter::class,
            'hotel.search.suppliers'
        );

        $this->app->tag(
            ExpediaHotelBookingAdapter::class,
            'hotel.booking.suppliers'
        );
    }
}
