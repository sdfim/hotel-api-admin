<?php

namespace Modules\HotelContentRepository\Services\Suppliers;

use App\Models\HiltonProperty;
use App\Models\Mapping;
use Modules\API\Suppliers\Transformers\Hilton\HiltonHotelContentDetailTransformer;
use Modules\HotelContentRepository\Services\SupplierInterface;

class HotelTraderContentApiService implements SupplierInterface
{
    public function __construct(
        protected readonly HiltonHotelContentDetailTransformer $hiltonHotelContentDetailTransformer,
    ) {}

    public function getResults(array $giataCodes): array
    {
        $hiltonCodes = Mapping::hilton()->whereIn('giata_id', $giataCodes)->pluck('giata_id', 'supplier_id')->toArray();
        $resultsHilton = HiltonProperty::whereIn('prop_code', array_keys($hiltonCodes))->get();

        $results = [];
        foreach ($resultsHilton as $item) {
            $giataId = $hiltonCodes[$item->prop_code];
            $contentDetailResponse = $this->hiltonHotelContentDetailTransformer->HiltonToContentDetailResponse($item, $giataId);
            $results = array_merge($results, $contentDetailResponse);
        }

        return $results;
    }
}
