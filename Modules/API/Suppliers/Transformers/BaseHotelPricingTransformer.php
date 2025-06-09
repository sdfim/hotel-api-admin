<?php

namespace Modules\API\Suppliers\Transformers;

use App\Models\DepositInformation;
use App\Models\MappingRoom;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Modules\API\Tools\PricingDtoTools;

class BaseHotelPricingTransformer
{
    private const CACHE_TTL_MINUTES = 1;

    protected array $ultimateAmenities = [];

    protected array $depositInformation = [];

    protected array $mapperSupplierRepository = [];

    protected array $repoTaxFees = [];

    protected array $unifiedRoomCodes = [];

    protected string $search_id = '';

    protected array $bookingItems = [];

    protected string $checkin = '';

    protected string $checkout = '';

    protected string $destinationData = '';

    protected array $giata = [];

    protected array $exclusionRates = [];

    protected array $exclusionRoomNames = [];

    protected array $exclusionRoomTypes = [];

    protected array $query = [];

    /**
     * Fetches and processes supplier repository data.
     * Uses Cache to store the processed data, ensuring it is shared
     * across all instances and subclasses to avoid redundant processing
     * when working with the same dataset.
     *
     * @param  string  $searchId  Unique identifier for the search.
     * @param  array  $giataIds  Array of Giata IDs to fetch data for.
     */
    public function fetchSupplierRepositoryData(string $searchId, array $giataIds): void
    {
        $cacheKey = 'supplier_data_'.$searchId;

        // Check if data is already cached
        $cachedData = Cache::get($cacheKey);
        if ($cachedData !== null) {
            $this->setPropertiesFromCache($cachedData);

            logger('Using cached supplier data', [
                'searchId' => $searchId,
                'cacheKey' => $cacheKey,
            ]);

            return;
        }

        // Fetch and process data
        $cachedData = $this->processSupplierData($giataIds);

        Cache::put($cacheKey, $cachedData, now()->addMinutes(self::CACHE_TTL_MINUTES));
    }

    private function processSupplierData(array $giataIds): array
    {
        // Fetch and process data
        $depositInformationData = DepositInformation::whereIn('giata_code', $giataIds)->get();

        $this->depositInformation = $depositInformationData->mapWithKeys(function ($hotel) {
            return [
                $hotel->giata_code => $hotel->depositInformations->map(function ($depositInformation) use ($hotel) {
                    return array_merge(
                        $depositInformation->toArray(),
                        [
                            'conditions' => $depositInformation->conditions->toArray(),
                            'hotel' => $hotel,
                        ]
                    );
                })->toArray(),
            ];
        })->toArray();

        $unifiedRoomCodesData = MappingRoom::whereIn('giata_id', $giataIds)->get();

        $this->unifiedRoomCodes = [];
        foreach ($unifiedRoomCodesData as $hotel) {
            $this->unifiedRoomCodes[$hotel->supplier][$hotel->giata_id][$hotel->supplier_room_code] = $hotel->unified_room_code;
        }

        return [
            'depositInformation' => $this->depositInformation,
            'unifiedRoomCodes' => $this->unifiedRoomCodes,
        ];
    }

    private function setPropertiesFromCache(array $cachedData): void
    {
        $this->unifiedRoomCodes = $cachedData['unifiedRoomCodes'];
    }

    protected function initializePricingData(array $query, array $pricingExclusionRules, array $giataIds, string $search_id): void
    {
        $this->search_id = $search_id;
        $this->bookingItems = [];
        $this->checkin = Arr::get($query, 'checkin', Carbon::today()->toDateString());
        $this->checkout = Arr::get($query, 'checkout', Carbon::today()->toDateString());

        $this->query = $query;

        $cacheKey = 'pricing_data_'.md5(json_encode([$giataIds, $search_id]));

        // Check if data is already cached
        $cachedData = Cache::get($cacheKey);
        if ($cachedData !== null) {
            $this->destinationData = $cachedData['destinationData'];
            $this->giata = $cachedData['giata'];
            $this->exclusionRates = $cachedData['exclusionRates'];
            $this->exclusionRoomNames = $cachedData['exclusionRoomNames'];
            $this->exclusionRoomTypes = $cachedData['exclusionRoomTypes'];
            $this->search_id = $search_id;

            return;
        }

        // Fetch and process data
        /* @var PricingDtoTools $pricingDtoTools */
        $pricingDtoTools = app(PricingDtoTools::class);
        $this->destinationData = $pricingDtoTools->getDestinationData($query) ?? '';
        $this->giata = $pricingDtoTools->getGiataProperties($query, $giataIds);
        $this->exclusionRates = $pricingDtoTools->extractExclusionValues($pricingExclusionRules, 'rate_code');
        $this->exclusionRoomNames = $pricingDtoTools->extractExclusionValues($pricingExclusionRules, 'room_name');
        $this->exclusionRoomTypes = $pricingDtoTools->extractExclusionValues($pricingExclusionRules, 'room_type');

        // Cache the processed data
        Cache::put($cacheKey, [
            'destinationData' => $this->destinationData,
            'giata' => $this->giata,
            'exclusionRates' => $this->exclusionRates,
            'exclusionRoomNames' => $this->exclusionRoomNames,
            'exclusionRoomTypes' => $this->exclusionRoomTypes,
        ], now()->addMinutes(self::CACHE_TTL_MINUTES));
    }

    protected function transformPricingRulesAppliers(array $pricingRulesApplier): array
    {
        $listRules = collect(Arr::get($pricingRulesApplier, 'validPricingRules', []))->map(function ($rule) {
            return [
                'main' => 'id: '.$rule['id'].' | name: '.$rule['name'].' | weight: '.$rule['weight'],
                'manipulable_price' => $rule['manipulable_price_type'].' '.$rule['price_value'].' '.$rule['price_value_type'].' '.$rule['price_value_target'],
                'conditions' => collect($rule['conditions'])->map(function ($condition) {
                    return "{$condition['field']} {$condition['compare']} ".($condition['value'] ?? "{$condition['value_from']} - {$condition['value_to']}");
                })->toArray(),
            ];
        })->toArray();

        return [
            'count' => count($listRules),
            'list' => $listRules,
        ];
    }
}
