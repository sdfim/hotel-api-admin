<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Modules\AdministrationSuite\Http\Controllers\HotelTraderController;
use Modules\API\Suppliers\Base\Transformers\SupplierContentTransformerInterface;
use Modules\API\Suppliers\Contracts\Hotel\Search\HotelSupplierInterface;
use Modules\API\Suppliers\Expedia\Adapters\ExpediaHotelAdapter;
use Modules\API\Suppliers\Expedia\Transformers\ExpediaHotelContentTransformer;
use Modules\API\Suppliers\Hilton\Adapters\HiltonHotelAdapter;
use Modules\API\Suppliers\Hilton\Transformers\HiltonHotelContentTransformer;
use Modules\API\Suppliers\HotelTrader\Adapters\HotelTraderAdapter;
use Modules\API\Suppliers\HotelTrader\Transformers\HotelTraderContentTransformer;
use Modules\API\Suppliers\IcePortal\Adapters\IcePortalHotelAdapter;
use Modules\API\Suppliers\IcePortal\Transformers\IcePortalHotelContentTransformer;
use Modules\Enums\SupplierNameEnum;
use Modules\HotelContentRepository\Services\SupplierInterface;
use Modules\HotelContentRepository\Services\Suppliers\ExpediaHotelContentApiService;
use Modules\HotelContentRepository\Services\Suppliers\HbsiHotelContentApiService;
use Modules\HotelContentRepository\Services\Suppliers\HiltonHotelContentApiService;
use Modules\HotelContentRepository\Services\Suppliers\HotelTraderContentApiService;
use Modules\HotelContentRepository\Services\Suppliers\IcePortalHotelContentApiService;

class SupplierServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(HotelSupplierInterface::class, function ($app, $params) {
            return match ($params['supplier']) {
                SupplierNameEnum::EXPEDIA->value => app(ExpediaHotelAdapter::class),
                SupplierNameEnum::ICE_PORTAL->value => app(IcePortalHotelAdapter::class),
                SupplierNameEnum::HILTON->value => app(HiltonHotelAdapter::class),
                SupplierNameEnum::HOTEL_TRADER->value => app(HotelTraderAdapter::class),
                default => throw new \InvalidArgumentException("Unknown supplier: {$params['supplier']}"),
            };
        });

        $this->app->bind(SupplierInterface::class, function ($app, $params) {
            return match ($params['supplier']) {
                SupplierNameEnum::EXPEDIA->value => app(ExpediaHotelContentApiService::class),
                SupplierNameEnum::ICE_PORTAL->value => app(IcePortalHotelContentApiService::class),
                SupplierNameEnum::HILTON->value => app(HiltonHotelContentApiService::class),
                SupplierNameEnum::HOTEL_TRADER->value => app(HotelTraderContentApiService::class),
                SupplierNameEnum::HBSI->value => app(HbsiHotelContentApiService::class),
                default => throw new \InvalidArgumentException("Unknown supplier: {$params['supplier']}"),
            };
        });

        $this->app->bind(SupplierContentTransformerInterface::class, function ($app, $params) {
            return match ($params['supplier']) {
                SupplierNameEnum::EXPEDIA->value => app(ExpediaHotelContentTransformer::class),
                SupplierNameEnum::ICE_PORTAL->value => app(IcePortalHotelContentTransformer::class),
                SupplierNameEnum::HILTON->value => app(HiltonHotelContentTransformer::class),
                SupplierNameEnum::HOTEL_TRADER->value => app(HotelTraderContentTransformer::class),
                default => throw new \InvalidArgumentException("Unknown supplier: {$params['supplier']}"),
            };
        });
    }
}
