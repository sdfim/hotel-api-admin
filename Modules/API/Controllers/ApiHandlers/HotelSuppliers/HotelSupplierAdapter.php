<?php

namespace Modules\API\Controllers\ApiHandlers\HotelSuppliers;

use Exception;
use Illuminate\Http\Request;
use Modules\Enums\SupplierNameEnum;

class HotelSupplierAdapter
{
    private array $controllers;

    public function __construct(
        HbsiHotelController $hbsi,
        ExpediaHotelController $expedia,
        HotelTraderController $hTrader,
        IcePortalHotelController $icePortal,
        HiltonHotelController $hilton,
    ) {
        $this->controllers = [
            SupplierNameEnum::HBSI->value => $hbsi,
            SupplierNameEnum::EXPEDIA->value => $expedia,
            SupplierNameEnum::HOTEL_TRADER->value => $hTrader,
            SupplierNameEnum::ICE_PORTAL->value => $icePortal,
            SupplierNameEnum::HILTON->value => $hilton,
        ];
    }

    public function preSearchData(string $supplierName, array &$filters, string $initiator): ?array
    {
        if (! isset($this->controllers[$supplierName])) {
            throw new Exception("Unknown supplier: $initiator");
        }
        /** @var HotelSupplierInterface $controller */
        $controller = $this->controllers[$supplierName];

        return $controller->preSearchData($filters, $initiator);
    }

    public function search(string $supplierName, array $filters): array
    {
        if (! isset($this->controllers[$supplierName])) {
            throw new Exception("Unknown supplier: $supplierName");
        }
        /** @var HotelSupplierInterface $controller */
        $controller = $this->controllers[$supplierName];

        return $controller->search($filters);
    }

    public function detail(string $supplierName, Request $request): array|object
    {
        if (! isset($this->controllers[$supplierName])) {
            throw new Exception("Unknown supplier: $supplierName");
        }
        /** @var HotelSupplierInterface $controller */
        $controller = $this->controllers[$supplierName];

        return $controller->detail($request);
    }

    public function price(
        string $supplierName,
        array $filters,
        array $searchInspector,
        array $rawGiataIds
    ): ?array {
        if (! isset($this->controllers[$supplierName])) {
            throw new Exception("Unknown supplier: $supplierName");
        }
        /** @var HotelSupplierInterface $controller */
        $controller = $this->controllers[$supplierName];

        return $controller->price($filters, $searchInspector, $rawGiataIds);
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
        if (! isset($this->controllers[$supplierName])) {
            throw new Exception("Unknown supplier: $supplierName");
        }

        /** @var HotelSupplierInterface $controller */
        $controller = $this->controllers[$supplierName];

        return $controller->processPriceResponse(
            $rawResponse,
            $filters,
            $searchId,
            $pricingRules,
            $pricingExclusionRules,
            $giataIds
        );
    }
}
