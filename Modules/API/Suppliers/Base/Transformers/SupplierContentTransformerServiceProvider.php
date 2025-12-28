<?php

namespace Modules\API\Suppliers\Base\Transformers;

use Illuminate\Support\ServiceProvider;

class SupplierContentTransformerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SupplierContentTransformerRegistry::class);
    }

    public function boot(): void
    {
        $registry = $this->app->make(SupplierContentTransformerRegistry::class);

        // Автоматически регистрируем всех поставщиков по тегу
        foreach ($this->app->tagged('hotel.content.transformers') as $supplier) {
            $registry->register($supplier);
        }
    }
}
