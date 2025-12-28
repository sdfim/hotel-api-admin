<?php

namespace Modules\API\Suppliers\Contracts\Hotel\ContentV1;

use Illuminate\Support\ServiceProvider;

class HotelContentV1ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(HotelContentV1SupplierRegistry::class);
    }

    public function boot(): void
    {
        $registry = $this->app->make(HotelContentV1SupplierRegistry::class);

        // Автоматически регистрируем всех поставщиков по тегу
        foreach ($this->app->tagged('hotel.content.v1.suppliers') as $supplier) {
            $registry->register($supplier);
        }
    }
}
