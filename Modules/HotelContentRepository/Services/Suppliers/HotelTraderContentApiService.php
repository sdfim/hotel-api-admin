<?php

namespace Modules\HotelContentRepository\Services\Suppliers;

use App\Models\HotelTraderProperty;
use App\Models\Mapping;
use Modules\API\Suppliers\Transformers\HotelTrader\HotelTraderContentDetailTransformer;
use Modules\HotelContentRepository\Services\SupplierInterface;

class HotelTraderContentApiService implements SupplierInterface
{
    public function __construct(
        protected readonly HotelTraderContentDetailTransformer $hotelTraderContentDetailTransformer
    ) {}

    public function getResults(array $giataCodes): array
    {
        $hotelTraderCodes = Mapping::hotelTrader()->whereIn('giata_id', $giataCodes)->pluck('giata_id', 'supplier_id')->toArray();
        $resultsHotelTrader = HotelTraderProperty::whereIn('propertyId', array_keys($hotelTraderCodes))->get();

        $results = [];
        foreach ($resultsHotelTrader as $item) {
            $giataId = $hotelTraderCodes[$item->propertyId];
            $contentDetailResponse = $this->hotelTraderContentDetailTransformer->HotelTraderToContentDetailResponse($item, $giataId);
            $results = array_merge($results, $contentDetailResponse);
        }

        return $results;
    }
}
