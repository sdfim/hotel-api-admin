<?php

namespace Modules\API\Suppliers\Contracts\Hotel\Search;

use Illuminate\Support\ServiceProvider;

class HotelSearchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Регистрируем Registry как singleton
        $this->app->singleton(HotelSupplierRegistry::class);
    }

    public function boot(): void
    {
        $registry = $this->app->make(HotelSupplierRegistry::class);

        // Автоматически регистрируем всех поставщиков по тегу
        foreach ($this->app->tagged('hotel.search.suppliers') as $supplier) {
            $registry->register($supplier);
        }
    }
}
