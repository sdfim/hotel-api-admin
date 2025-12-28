<?php

namespace Modules\API\Suppliers\HBSI;

use Illuminate\Support\ServiceProvider;
use Modules\API\Suppliers\HBSI\Adapters\HbsiHotelAdapter;
use Modules\API\Suppliers\HBSI\Adapters\HbsiHotelBookingAdapter;
use Modules\API\Suppliers\HBSI\Adapters\HbsiHotelContentV1Apapter;

class HbsiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->tag(
            HbsiHotelAdapter::class,
            'hotel.search.suppliers'
        );

        $this->app->tag(
            HbsiHotelBookingAdapter::class,
            'hotel.booking.suppliers'
        );

        $this->app->tag(
            HbsiHotelContentV1Apapter::class,
            'hotel.content.v1.suppliers'
        );
    }
}
