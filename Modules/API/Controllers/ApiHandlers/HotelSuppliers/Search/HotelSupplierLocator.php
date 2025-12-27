<?php

namespace Modules\API\Controllers\ApiHandlers\HotelSuppliers\Search;

use Exception;
use Illuminate\Http\Request;
use Modules\Enums\SupplierNameEnum;

class HotelSupplierLocator
{
    private array $controllers;

    public function __construct(
        HbsiHotelAdapter $hbsi,
        ExpediaHotelAdapter $expedia,
        HotelTraderAdapter $hTrader,
        IcePortalHotelAdapter $icePortal,
        HiltonHotelAdapter $hilton,
    ) {
        $this->controllers = [
            SupplierNameEnum::HBSI->value => $hbsi,
            SupplierNameEnum::EXPEDIA->value => $expedia,
            SupplierNameEnum::HOTEL_TRADER->value => $hTrader,
            SupplierNameEnum::ICE_PORTAL->value => $icePortal,
            SupplierNameEnum::HILTON->value => $hilton,
        ];
    }

    public function getController(string $supplierName): HotelSupplierInterface
    {
        /* @var HotelSupplierInterface $controller */
        if (! isset($this->controllers[$supplierName])) {
            throw new Exception("Unknown supplier: $supplierName");
        }

        return $this->controllers[$supplierName];
    }

    public function preSearchData(string $supplierName, array &$filters, string $initiator): ?array
    {
        return $this->getController($supplierName)->preSearchData($filters, $initiator);
    }

    public function search(string $supplierName, array $filters): array
    {
        return $this->getController($supplierName)->search($filters);
    }

    public function detail(string $supplierName, Request $request): array|object
    {
        return $this->getController($supplierName)->detail($request);
    }

    public function price(
        string $supplierName,
        array $filters,
        array $searchInspector,
        array $rawGiataIds
    ): ?array {
        return $this->getController($supplierName)->price($filters, $searchInspector, $rawGiataIds);
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
        return $this->getController($supplierName)->processPriceResponse(
            $rawResponse,
            $filters,
            $searchId,
            $pricingRules,
            $pricingExclusionRules,
            $giataIds
        );
    }
}
