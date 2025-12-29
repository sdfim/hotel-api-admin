<?php

namespace Modules\API\Suppliers\HotelTrader;

use Illuminate\Support\ServiceProvider;
use Modules\API\Suppliers\HotelTrader\Adapters\HotelTraderAdapter;
use Modules\API\Suppliers\HotelTrader\Adapters\HotelTraderHotelBookingAdapter;
use Modules\API\Suppliers\HotelTrader\Transformers\HotelTraderContentTransformer;

class HotelTraderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->tag(
            HotelTraderAdapter::class,
            'hotel.search.suppliers'
        );

        $this->app->tag(
            HotelTraderHotelBookingAdapter::class,
            'hotel.booking.suppliers'
        );

        $this->app->tag(
            HotelTraderContentTransformer::class,
            'hotel.content.transformers'
        );
    }
}
