<?php

namespace Modules\API\Suppliers\IcePortal;

use Illuminate\Support\ServiceProvider;
use Modules\API\Suppliers\IcePortal\Adapters\IcePortalHotelAdapter;
use Modules\API\Suppliers\IcePortal\Transformers\IcePortalHotelContentTransformer;

class IcePortalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->tag(
            IcePortalHotelAdapter::class,
            'hotel.search.suppliers'
        );

        $this->app->tag(
            IcePortalHotelContentTransformer::class,
            'hotel.content.transformers'
        );
    }
}
