<?php

namespace Modules\API\Suppliers\Base\Transformers;

use Modules\Enums\SupplierNameEnum;
use Exception;
use RuntimeException;

class SupplierContentTransformerRegistry
{
    /** @var array<string, SupplierContentTransformerInterface> */
    private array $suppliers = [];

    public function register(SupplierContentTransformerInterface $supplier): void
    {
        $key = $supplier->supplier()->value;

        if (isset($this->suppliers[$key])) {
            throw new RuntimeException("Supplier {$key} already registered");
        }

        $this->suppliers[$key] = $supplier;
    }

    public function get(string|SupplierNameEnum $name): SupplierContentTransformerInterface
    {
        $key = $name instanceof SupplierNameEnum ? $name->value : $name;

        return $this->suppliers[$key]
            ?? throw new Exception("Supplier {$key} not registered in SupplierContentTransformerRegistry");
    }

    public function has(string|SupplierNameEnum $name): bool
    {
        $key = $name instanceof SupplierNameEnum ? $name->value : $name;

        return isset($this->suppliers[$key]);
    }

    /** @return array<string, SupplierContentTransformerInterface> */
    public function all(): array
    {
        return $this->suppliers;
    }
}
