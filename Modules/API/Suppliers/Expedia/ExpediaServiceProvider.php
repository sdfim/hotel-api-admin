<?php

namespace Modules\API\Suppliers\Expedia;

use Illuminate\Support\ServiceProvider;
use Modules\API\Suppliers\Expedia\Adapters\ExpediaHotelAdapter;
use Modules\API\Suppliers\Expedia\Adapters\ExpediaHotelBookingAdapter;
use Modules\API\Suppliers\Expedia\Adapters\ExpediaHotelContentV1Adapter;
use Modules\API\Suppliers\Expedia\Transformers\ExpediaHotelContentTransformer;

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

        $this->app->tag(
            ExpediaHotelContentV1Adapter::class,
            'hotel.content.v1.suppliers'
        );

        $this->app->tag(
            ExpediaHotelContentTransformer::class,
            'hotel.content.transformers'
        );
    }
}
