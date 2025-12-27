<?php

namespace Modules\API\Suppliers\Contracts\Hotel\Search;

use Exception;
use Illuminate\Http\Request;
use Modules\API\Suppliers\Expedia\Adapters\ExpediaHotelAdapter;
use Modules\API\Suppliers\HBSI\Adapters\HbsiHotelAdapter;
use Modules\API\Suppliers\Hilton\Adapters\HiltonHotelAdapter;
use Modules\API\Suppliers\HotelTrader\Adapters\HotelTraderAdapter;
use Modules\API\Suppliers\IcePortal\Adapters\IcePortalHotelAdapter;
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

    public function getAdapter(string $supplierName): HotelSupplierInterface
    {
        /* @var HotelSupplierInterface $controller */
        if (! isset($this->controllers[$supplierName])) {
            throw new Exception("Unknown supplier: $supplierName");
        }

        return $this->controllers[$supplierName];
    }

    public function preSearchData(string $supplierName, array &$filters, string $initiator): ?array
    {
        return $this->getAdapter($supplierName)->preSearchData($filters, $initiator);
    }

    public function search(string $supplierName, array $filters): array
    {
        return $this->getAdapter($supplierName)->search($filters);
    }

    public function detail(string $supplierName, Request $request): array|object
    {
        return $this->getAdapter($supplierName)->detail($request);
    }

    public function price(
        string $supplierName,
        array $filters,
        array $searchInspector,
        array $rawGiataIds
    ): ?array {
        return $this->getAdapter($supplierName)->price($filters, $searchInspector, $rawGiataIds);
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
        return $this->getAdapter($supplierName)->processPriceResponse(
            $rawResponse,
            $filters,
            $searchId,
            $pricingRules,
            $pricingExclusionRules,
            $giataIds
        );
    }
}
