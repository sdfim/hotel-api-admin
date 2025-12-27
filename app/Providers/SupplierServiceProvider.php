<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\API\Controllers\ApiHandlers\HotelSuppliers\HotelTraderController;
use Modules\API\Controllers\ApiHandlers\HotelSuppliers\IcePortalHotelController;
use Modules\API\Controllers\ApiHandlers\HotelSuppliers\Search\ExpediaHotelAdapter;
use Modules\API\Controllers\ApiHandlers\HotelSuppliers\Search\HiltonHotelAdapter;
use Modules\API\Controllers\ApiHandlers\HotelSuppliers\Search\HotelSupplierInterface;
use Modules\API\Suppliers\Transformers\Expedia\ExpediaHotelContentTransformer;
use Modules\API\Suppliers\Transformers\Hilton\HiltonHotelContentTransformer;
use Modules\API\Suppliers\Transformers\HotelTrader\HotelTraderContentTransformer;
use Modules\API\Suppliers\Transformers\IcePortal\IcePortalHotelContentTransformer;
use Modules\API\Suppliers\Transformers\SupplierContentTransformerInterface;
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
                SupplierNameEnum::ICE_PORTAL->value => app(IcePortalHotelController::class),
                SupplierNameEnum::HILTON->value => app(HiltonHotelAdapter::class),
                SupplierNameEnum::HOTEL_TRADER->value => app(HotelTraderController::class),
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
