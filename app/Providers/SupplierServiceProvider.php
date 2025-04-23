<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\API\Controllers\ApiHandlers\ContentSuppliers\ExpediaHotelController;
use Modules\API\Controllers\ApiHandlers\ContentSuppliers\IcePortalHotelController;
use Modules\API\Controllers\ApiHandlers\ContentSuppliers\SupplierControllerInterface;
use Modules\API\Suppliers\Transformers\Expedia\ExpediaHotelContentTransformer;
use Modules\API\Suppliers\Transformers\IcePortal\IcePortalHotelContentTransformer;
use Modules\API\Suppliers\Transformers\SupplierContentTransformerInterface;
use Modules\Enums\SupplierNameEnum;
use Modules\HotelContentRepository\Services\SupplierInterface;
use Modules\HotelContentRepository\Services\Suppliers\ExpediaHotelContentApiService;
use Modules\HotelContentRepository\Services\Suppliers\IcePortalHotelContentApiService;

class SupplierServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(SupplierControllerInterface::class, function ($app, $params) {
            return match ($params['supplier']) {
                SupplierNameEnum::EXPEDIA->value => app(ExpediaHotelController::class),
                SupplierNameEnum::ICE_PORTAL->value => app(IcePortalHotelController::class),
                default => throw new \InvalidArgumentException("Unknown supplier: {$params['supplier']}"),
            };
        });

        $this->app->bind(SupplierInterface::class, function ($app, $params) {
            return match ($params['supplier']) {
                SupplierNameEnum::EXPEDIA->value => app(ExpediaHotelContentApiService::class),
                SupplierNameEnum::ICE_PORTAL->value => app(IcePortalHotelContentApiService::class),
                default => throw new \InvalidArgumentException("Unknown supplier: {$params['supplier']}"),
            };
        });

        $this->app->bind(SupplierContentTransformerInterface::class, function ($app, $params) {
            return match ($params['supplier']) {
                SupplierNameEnum::EXPEDIA->value => app(ExpediaHotelContentTransformer::class),
                SupplierNameEnum::ICE_PORTAL->value => app(IcePortalHotelContentTransformer::class),
                default => throw new \InvalidArgumentException("Unknown supplier: {$params['supplier']}"),
            };
        });
    }
}
