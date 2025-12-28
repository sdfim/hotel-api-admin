<?php

namespace Modules\API\Suppliers\Contracts\Hotel\Search;

use Exception;
use Illuminate\Http\Request;
use Modules\Enums\SupplierNameEnum;
use RuntimeException;

class HotelSupplierRegistry
{
    /** @var array<string, HotelSupplierInterface> */
    private array $suppliers = [];

    public function register(HotelSupplierInterface $supplier): void
    {
        $key = $supplier->supplier()->value;

        if (isset($this->suppliers[$key])) {
            throw new RuntimeException("Supplier {$key} already registered");
        }

        $this->suppliers[$key] = $supplier;
    }

    public function get(string|SupplierNameEnum $name): HotelSupplierInterface
    {
        $key = $name instanceof SupplierNameEnum ? $name->value : $name;

        return $this->suppliers[$key]
            ?? throw new Exception("Supplier {$key} not registered in HotelSupplierRegistry");
    }

    public function has(string|SupplierNameEnum $name): bool
    {
        $key = $name instanceof SupplierNameEnum ? $name->value : $name;
        return isset($this->suppliers[$key]);
    }

    /** @return array<string, HotelSupplierInterface> */
    public function all(): array
    {
        return $this->suppliers;
    }

    public function preSearchData(string $supplierName, array &$filters, string $initiator): ?array
    {
        return $this->get($supplierName)->preSearchData($filters, $initiator);
    }

    public function search(string $supplierName, array $filters): array
    {
        return $this->get($supplierName)->search($filters);
    }

    public function detail(string $supplierName, Request $request): array|object
    {
        return $this->get($supplierName)->detail($request);
    }

    public function price(
        string $supplierName,
        array $filters,
        array $searchInspector,
        array $rawGiataIds
    ): ?array {
        return $this->get($supplierName)->price($filters, $searchInspector, $rawGiataIds);
    }

    public function processPriceResponse(
        string $supplierName,
        array $rawResponse,
        array $filters,
        string $searchId,
        array $pricingRules,
        array $pricingExclusionRules,
        array $giataIds
    ): array {
        return $this->get($supplierName)->processPriceResponse(
            $rawResponse,
            $filters,
            $searchId,
            $pricingRules,
            $pricingExclusionRules,
            $giataIds
        );
    }
}
