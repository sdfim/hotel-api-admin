<?php

namespace Modules\API\Suppliers\Transformers;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Modules\API\Tools\PricingDtoTools;
use Modules\Enums\ContentSourceEnum;
use Modules\HotelContentRepository\Models\Hotel;

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

    protected array $query = [
        'force_on_sale' => false,
        'force_verified' => false,
    ];

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
        $supplierRepositoryData = Hotel::has('rooms')->whereIn('giata_code', $giataIds)->get();

        $this->ultimateAmenities = $supplierRepositoryData->mapWithKeys(function ($hotel) {
            return [
                $hotel->giata_code => $hotel->product?->affiliations->map(function ($affiliation) {
                    return [
                        'rate_code' => $affiliation->rate?->code,
                        'unified_room_code' => $affiliation->room?->external_code,
                        'start_date' => $affiliation->start_date,
                        'end_date' => $affiliation->end_date,
                        'amenities' => $affiliation->amenities->map(function ($amenity) {
                            $amenityData = [
                                'name' => $amenity->amenity->name,
                                'consortia' => $amenity->consortia,
                                'is_paid' => $amenity->is_paid,
                                'min_night_stay' => $amenity->min_night_stay,
                                'max_night_stay' => $amenity->max_night_stay,
                                'priority_rooms' => (! empty($amenity->priority_rooms))
                                    ? $amenity?->priorityRooms()->pluck('external_code')->toArray() ?? []
                                    : [],
                            ];
                            if ($amenity->is_paid) {
                                $amenityData['price'] = $amenity->price;
                                $amenityData['apply_type'] = $amenity->apply_type;
                            }

                            return $amenityData;
                        })->toArray(),
                    ];
                })->toArray(),
            ];
        })->toArray();

        $this->depositInformation = $supplierRepositoryData->mapWithKeys(function ($hotel) {
            return [
                $hotel->giata_code => $hotel->product?->depositInformations->map(function ($depositInformation) use ($hotel) {
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

        $this->mapperSupplierRepository = $supplierRepositoryData->mapWithKeys(function ($hotel) {
            return [
                $hotel->giata_code => $hotel->rooms->mapWithKeys(function ($room) {
                    if (! empty($room->external_code)) {
                        return [
                            $room->external_code => [
                                'description' => $room->description,
                                'name' => $room->name,
                            ],
                        ];
                    }

                    return [];
                })->toArray(),
            ];
        })->toArray();

        $this->repoTaxFees = $supplierRepositoryData->mapWithKeys(function ($hotel) {
            return [
                $hotel->giata_code => $hotel->product->feeTaxes->groupBy('action_type')->map(function ($group) {
                    return $group->mapWithKeys(function ($feeTax) {
                        $feeTaxData = $feeTax->toArray();
                        $feeTaxData['rate_code'] = null;
                        if ($feeTax->rate_id !== null) {
                            $feeTaxData['rate_code'] = $feeTax->rate->code;
                        }
                        $feeTaxData['unified_room_code'] = null;
                        if ($feeTax->room_id !== null) {
                            $feeTaxData['unified_room_code'] = $feeTax->room->external_code;
                        }

                        return [$feeTax->id => $feeTaxData];
                    })->toArray();
                })->toArray(),
            ];
        })->toArray();

        $this->unifiedRoomCodes = [];
        foreach ($supplierRepositoryData as $hotel) {
            // Skip hotels that are not on sale if force_on_sale is false
            if (! $this->query['force_on_sale'] && ! $hotel->product->onSale) {
                continue;
            }

            $hbsiHotelData = $expediaHotelData = [
                'hotel_code' => $hotel->giata_code,
                'rooms' => [],
            ];
            foreach ($hotel->rooms as $room) {
                $hbsiCode = collect(json_decode($room->supplier_codes, true))->filter(function ($code) {
                    return $code['supplier'] === ContentSourceEnum::HBSI->value;
                })->first()['code'] ?? null;
                $expediaCode = collect(json_decode($room->supplier_codes, true))->filter(function ($code) {
                    return $code['supplier'] === ContentSourceEnum::EXPEDIA->value;
                })->first()['code'] ?? null;
                if ($hbsiCode) {
                    $hbsiHotelData['rooms'][$hbsiCode] = $room->external_code;
                }
                if ($expediaCode) {
                    $expediaHotelData['rooms'][$expediaCode] = $room->external_code;
                }
            }
            $this->unifiedRoomCodes[ContentSourceEnum::HBSI->value][$hotel->giata_code] = $hbsiHotelData['rooms'];
            $this->unifiedRoomCodes[ContentSourceEnum::EXPEDIA->value][$hotel->giata_code] = $expediaHotelData['rooms'];
        }

        return [
            'ultimateAmenities' => $this->ultimateAmenities,
            'depositInformation' => $this->depositInformation,
            'mapperSupplierRepository' => $this->mapperSupplierRepository,
            'repoTaxFees' => $this->repoTaxFees,
            'unifiedRoomCodes' => $this->unifiedRoomCodes,
        ];
    }

    private function setPropertiesFromCache(array $cachedData): void
    {
        $this->ultimateAmenities = $cachedData['ultimateAmenities'];
        $this->depositInformation = $cachedData['depositInformation'];
        $this->mapperSupplierRepository = $cachedData['mapperSupplierRepository'];
        $this->repoTaxFees = $cachedData['repoTaxFees'];
        $this->unifiedRoomCodes = $cachedData['unifiedRoomCodes'];
    }

    protected function initializePricingData(array $query, array $pricingExclusionRules, array $giataIds, string $search_id): void
    {
        $this->search_id = $search_id;
        $this->bookingItems = [];
        $this->checkin = Arr::get($query, 'checkin', Carbon::today()->toDateString());
        $this->checkout = Arr::get($query, 'checkout', Carbon::today()->toDateString());

        $this->query = array_merge([
            'force_on_sale' => false,
            'force_verified' => false,
        ], $query);

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
