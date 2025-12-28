<?php

namespace Modules\API\Suppliers\IcePortal;

use Illuminate\Support\ServiceProvider;
use Modules\API\Suppliers\IcePortal\Adapters\IcePortalHotelAdapter;

class IcePortalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->tag(
            IcePortalHotelAdapter::class,
            'hotel.search.suppliers'
        );
    }
}
