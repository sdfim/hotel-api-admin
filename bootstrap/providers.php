<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,
    //App\Providers\HorizonServiceProvider::class,
    App\Providers\JetstreamServiceProvider::class,
    App\Providers\PermissionServiceProvider::class,
    App\Providers\RolesServiceProvider::class,
    App\Providers\SupplierRepositoryServiceProvider::class,
    App\Providers\RouteServiceProvider::class,
    BladeUIKit\BladeUIKitServiceProvider::class,

    Modules\API\Suppliers\Contracts\Hotel\Search\HotelSearchServiceProvider::class,
    Modules\API\Suppliers\Contracts\Hotel\Booking\HotelBookingServiceProvider::class,
    Modules\API\Suppliers\Base\Transformers\SupplierContentTransformerServiceProvider::class,

    Modules\API\Suppliers\HBSI\HbsiServiceProvider::class,
    Modules\API\Suppliers\Expedia\ExpediaServiceProvider::class,
    Modules\API\Suppliers\HotelTrader\HotelTraderServiceProvider::class,
    Modules\API\Suppliers\Oracle\OracleServiceProvider::class,
    Modules\API\Suppliers\IcePortal\IcePortalServiceProvider::class,
    Modules\API\Suppliers\Hilton\HiltonServiceProvider::class,
];
