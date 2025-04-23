<?php

namespace Modules\HotelContentRepository\Services\Suppliers;

use Modules\API\Controllers\ApiHandlers\ContentSuppliers\IcePortalHotelController;
use Modules\API\Suppliers\Transformers\IcePortal\IcePortalHotelContentDetailTransformer;
use Modules\HotelContentRepository\Services\SupplierInterface;

class IcePortalHotelContentApiService implements SupplierInterface
{
    public function __construct(
        protected readonly IcePortalHotelController $icePortal,
        protected readonly IcePortalHotelContentDetailTransformer $icePortalHotelContentDetailTransformer,
    ) {}

    public function getResults(array $giataCodes): array
    {
        $resultsIcePortal = $this->icePortal->details($giataCodes);

        $results = [];
        foreach ($resultsIcePortal as $giataId => $item) {
            $contentDetailResponse = $this->icePortalHotelContentDetailTransformer
                ->HbsiToContentDetailResponseWithAssets($item, $giataId, $item['assets']);
            $results = array_merge($results, $contentDetailResponse);
        }

        return $results;
    }
}
