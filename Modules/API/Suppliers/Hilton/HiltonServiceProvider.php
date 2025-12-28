<?php

namespace Modules\API\Suppliers\Hilton;

use Illuminate\Support\ServiceProvider;
use Modules\API\Suppliers\Hilton\Adapters\HiltonHotelAdapter;

class HiltonServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->tag(
            HiltonHotelAdapter::class,
            'hotel.search.suppliers'
        );
    }
}
