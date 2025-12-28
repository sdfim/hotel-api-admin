<?php

namespace Modules\API\Suppliers\Hilton;

use Illuminate\Support\ServiceProvider;
use Modules\API\Suppliers\Hilton\Adapters\HiltonHotelAdapter;
use Modules\API\Suppliers\Hilton\Adapters\HiltonHotelContentV1Adapter;
use Modules\API\Suppliers\Hilton\Transformers\HiltonHotelContentTransformer;

class HiltonServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->tag(
            HiltonHotelAdapter::class,
            'hotel.search.suppliers'
        );

        $this->app->tag(
            HiltonHotelContentV1Adapter::class,
            'hotel.content.v1.suppliers'
        );

        $this->app->tag(
            HiltonHotelContentTransformer::class,
            'hotel.content.transformers'
        );
    }
}
