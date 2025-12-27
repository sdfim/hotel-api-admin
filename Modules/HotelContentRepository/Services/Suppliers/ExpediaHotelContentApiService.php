<?php

namespace Modules\HotelContentRepository\Services\Suppliers;

use App\Models\ExpediaContent;
use App\Models\ExpediaContentSlave;
use Illuminate\Support\Arr;
use Modules\API\Services\MappingCacheService;
use Modules\API\Suppliers\Expedia\Transformers\ExpediaHotelContentDetailTransformer;
use Modules\HotelContentRepository\Services\SupplierInterface;

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

    public function getRoomsData(int $giataCode): array
    {
        $roomsData = [];

        /** @var MappingCacheService $mappingCacheService */
        $mappingCacheService = app(MappingCacheService::class);
        $hashMapExpedia = $mappingCacheService->getMappingsExpediaHashMap();
        $reversedHashMap = array_flip($hashMapExpedia);
        $expediaCode = $reversedHashMap[$giataCode] ?? null;

        $expediaData = ExpediaContentSlave::select('rooms', 'statistics', 'all_inclusive', 'amenities', 'attributes', 'themes', 'rooms_occupancy')
            ->where('expedia_property_id', $expediaCode)
            ->first();
        $expediaData = $expediaData ? $expediaData->toArray() : [];

        if (! empty($expediaData)) {
            $roomsData = Arr::get($expediaData, 'rooms', []);
        }

        return $roomsData;
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
