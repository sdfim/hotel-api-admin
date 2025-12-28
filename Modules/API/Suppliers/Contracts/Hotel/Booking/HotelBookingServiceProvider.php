<?php

namespace Modules\API\Suppliers\Contracts\Hotel\Booking;

use Illuminate\Support\ServiceProvider;

class HotelBookingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(HotelBookingSupplierRegistry::class);
    }

    public function boot(): void
    {
        $registry = $this->app->make(HotelBookingSupplierRegistry::class);

        // Автоматически регистрируем всех поставщиков по тегу
        foreach ($this->app->tagged('hotel.booking.suppliers') as $supplier) {
            $registry->register($supplier);
        }
    }
}
