<?php

namespace App\Services\Suppliers;

use App\Models\ExpediaContent;
use Modules\API\Services\MappingCacheService;
use Modules\API\Suppliers\Transformers\Expedia\ExpediaHotelContentDetailTransformer;
use App\Services\SupplierInterface;

class ExpediaHotelContentApiService implements SupplierInterface
{
    public function __construct(
        protected readonly MappingCacheService $mappingCacheService,
        protected readonly ExpediaHotelContentDetailTransformer $expediaHotelContentDetailTransformer,
    ) {}

    public function getResults(array $giataCodes): array
    {
        $resultsExpedia = [];
        $mappingsExpedia = $this->mappingCacheService->getMappingsExpediaHashMap();
        $expediaCodes = $this->getExpediaCodes($giataCodes, $mappingsExpedia);
        $expediaData = $this->getExpediaData($expediaCodes);

        foreach ($expediaData as $item) {
            if (! isset($item->expediaSlave)) {
                continue;
            }
            foreach ($item->expediaSlave->getAttributes() as $key => $value) {
                if (is_string($value)) {
                    $value = json_decode($value, true);
                }
                $item->$key = $value;
            }
            $contentDetailResponse = $this->expediaHotelContentDetailTransformer->ExpediaToContentDetailResponse($item->toArray(), $mappingsExpedia[$item->property_id]);
            $resultsExpedia = array_merge($resultsExpedia, $contentDetailResponse);
        }

        return $resultsExpedia;
    }

    private function getExpediaCodes(array $giataCodes, array $mappingsExpedia): array
    {
        $expediaCodes = [];
        foreach ($giataCodes as $giataCode) {
            $expediaCode = array_search($giataCode, $mappingsExpedia);
            if ($expediaCode !== false) {
                $expediaCodes[] = $expediaCode;
            }
        }

        return $expediaCodes;
    }

    private function getExpediaData(array $expediaCodes)
    {
        return ExpediaContent::with('expediaSlave')
            ->whereIn('property_id', $expediaCodes)
            ->get();
    }
}
