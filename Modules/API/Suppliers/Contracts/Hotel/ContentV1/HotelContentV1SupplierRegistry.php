<?php

namespace Modules\API\Suppliers\Contracts\Hotel\ContentV1;

use Exception;
use Modules\Enums\SupplierNameEnum;
use RuntimeException;

class HotelContentV1SupplierRegistry
{
    /** @var array<string, HotelContentV1SupplierInterface> */
    private array $suppliers = [];

    public function register(HotelContentV1SupplierInterface $supplier): void
    {
        $key = $supplier->supplier()->value;

        if (isset($this->suppliers[$key])) {
            throw new RuntimeException("Supplier {$key} already registered");
        }

        $this->suppliers[$key] = $supplier;
    }

    public function get(string|SupplierNameEnum $name): HotelContentV1SupplierInterface
    {
        $key = $name instanceof SupplierNameEnum ? $name->value : $name;

        return $this->suppliers[$key]
            ?? throw new Exception("Supplier {$key} not registered in HotelContentV1SupplierRegistry");
    }

    public function has(string|SupplierNameEnum $name): bool
    {
        $key = $name instanceof SupplierNameEnum ? $name->value : $name;

        return isset($this->suppliers[$key]);
    }

    /** @return array<string, HotelContentV1SupplierInterface> */
    public function all(): array
    {
        return $this->suppliers;
    }
}
