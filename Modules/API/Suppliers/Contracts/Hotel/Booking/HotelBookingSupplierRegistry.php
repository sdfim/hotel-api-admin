<?php

namespace Modules\API\Suppliers\Contracts\Hotel\Booking;

use Modules\Enums\SupplierNameEnum;
use RuntimeException;
use Exception;

class HotelBookingSupplierRegistry
{
    /** @var array<string, HotelBookingSupplierInterface> */
    private array $suppliers = [];

    public function register(HotelBookingSupplierInterface $supplier): void
    {
        $key = $supplier->supplier()->value;

        if (isset($this->suppliers[$key])) {
            throw new RuntimeException("Supplier {$key} already registered");
        }

        $this->suppliers[$key] = $supplier;
    }

    public function get(SupplierNameEnum $name): HotelBookingSupplierInterface
    {
        return $this->suppliers[$name->value]
            ?? throw new Exception("Supplier {$name->value} not registered in HotelBookingSupplierRegistry");
    }

    public function has(SupplierNameEnum $name): bool
    {
        return isset($this->suppliers[$name->value]);
    }

    /** @return array<string, HotelBookingSupplierInterface> */
    public function all(): array
    {
        return $this->suppliers;
    }
}

