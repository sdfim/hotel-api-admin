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

    protected array $priorityContentFromSupplierRepo = [];

    protected array $depositInformation = [];

    protected array $descriptiveContent = [];

    protected array $cancellationPolicies = [];

    protected array $mapperSupplierRepository = [];

    protected array $rates = [];

    protected array $repoServices = [];

    protected array $features = [];

    protected array $unifiedRoomCodes = [];

    protected array $roomIdByUnifiedCode = [];

    protected array $basicHotelData = [];

    protected string $search_id = '';

    protected array $bookingItems = [];

    protected string $checkin = '';

    protected string $checkout = '';

    protected array $occupancy = [];

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

        $this->depositInformation = $supplierRepositoryData->mapWithKeys(function ($hotel) {
            return [
                $hotel->giata_code => (isset($hotel->product) && isset($hotel->product->depositInformations) ? $hotel->product->depositInformations->map(function ($depositInformation) use ($hotel) {
                    $content = $depositInformation->toArray();
                    if (isset($content['rate_id']) && isset($depositInformation->rate)) {
                        $content['rate_id'] = $depositInformation->rate->code;
                    }

                    return array_merge(
                        $content,
                        [
                            'conditions' => $depositInformation->conditions->toArray(),
                            'hotel' => $hotel,
                        ]
                    );
                })->toArray() : []),
            ];
        })->toArray();

        $this->descriptiveContent = $supplierRepositoryData->mapWithKeys(function ($hotel) {
            return [
                $hotel->giata_code => $hotel->product?->descriptiveContentsSection->map(function ($descriptiveContent) {
                    $content = $descriptiveContent->toArray();
                    if (isset($content['rate_id']) && $descriptiveContent->rate) {
                        $content['rate_id'] = $descriptiveContent->rate->code;
                    }
                    if (isset($content['descriptive_type_id']) && $descriptiveContent->descriptiveType) {
                        $content['descriptive_type_name'] = $descriptiveContent->descriptiveType->name;
                        $content['descriptive_type_description'] = $descriptiveContent->descriptiveType->description;
                        $content['descriptive_type'] = $descriptiveContent->descriptiveType->type;
                        $content['descriptive_type_location'] = $descriptiveContent->descriptiveType->location;
                    }

                    return $content;
                })->toArray(),
            ];
        })->toArray();

//        $this->cancellationPolicies = $supplierRepositoryData->mapWithKeys(function ($hotel) {
//            return [
//                $hotel->giata_code => $hotel->product?->cancellationPolicies->map(function ($policy) {
//                    $content = $policy->toArray();
//                    if (isset($content['rate_id']) && $policy->rate) {
//                        $content['rate_id'] = $policy->rate->code;
//                    }
//
//                    return $content;
//                })->toArray(),
//            ];
//        })->toArray();

        $this->rates = $supplierRepositoryData->mapWithKeys(function ($hotel) {
            return [
                $hotel->giata_code => $hotel?->rates?->toArray() ?? [],
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

//        $this->repoServices = $supplierRepositoryData->mapWithKeys(function ($hotel) {
//            return [
//                $hotel->giata_code => $hotel->product->informativeServices->map(function ($service) {
//                    $feeTaxData = $service->toArray();
//                    $feeTaxData['rate_code'] = null;
//                    if ($service->rate_id !== null) {
//                        $feeTaxData['rate_code'] = $service->rate->code;
//                    }
//                    $feeTaxData['unified_room_code'] = null;
//                    if ($service->room_id !== null) {
//                        $feeTaxData['unified_room_code'] = $service->room->external_code;
//                    }
//
//                    return $feeTaxData;
//                })->toArray(),
//            ];
//        })->toArray();

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

        $this->roomIdByUnifiedCode = [];
        foreach ($supplierRepositoryData as $hotel) {
            // Skip hotels that are not on sale if force_on_sale is false
            if (! $this->query['force_on_sale'] && ! $hotel->product->onSale) {
                continue;
            }
            foreach ($hotel->rooms as $room) {
                if (! empty($room->external_code)) {
                    $this->roomIdByUnifiedCode[$hotel->giata_code][$room->external_code] = $room->id;
                }
            }
        }

        $this->features = [];
        foreach ($supplierRepositoryData as $hotel) {
            $this->features[$hotel->giata_code] = [
                'holdable' => $hotel->holdable,
            ];
        };
        // Map the rating for each hotel by giata
        $this->priorityContentFromSupplierRepo = $supplierRepositoryData->mapWithKeys(function ($hotel) {
            return [
                $hotel->giata_code => [
                    'rating' => $hotel->star_rating,
                ],
            ];
        })->toArray();

        return [
            'priorityContentFromSupplierRepo' => $this->priorityContentFromSupplierRepo,
            'depositInformation' => $this->depositInformation,
            'descriptiveContent' => $this->descriptiveContent,
            'cancellationPolicies' => $this->cancellationPolicies,
            'mapperSupplierRepository' => $this->mapperSupplierRepository,
            'repoServices' => $this->repoServices,
            'basicHotelData' => $this->basicHotelData,
            'unifiedRoomCodes' => $this->unifiedRoomCodes,
            'roomIdByUnifiedCode' => $this->roomIdByUnifiedCode,
            'features' => $this->features,
            'rates' => $this->rates,
        ];
    }

    protected function getAttributeFromHotelOrProduct(string $giata, string $key)
    {
        return (isset($this->priorityContentFromSupplierRepo[$giata]) && isset($this->priorityContentFromSupplierRepo[$giata][$key]))
            ? $this->priorityContentFromSupplierRepo[$giata][$key]
            : ($this->giata[$giata][$key] ?? 0);
    }

    private function setPropertiesFromCache(array $cachedData): void
    {
        $this->priorityContentFromSupplierRepo = $cachedData['priorityContentFromSupplierRepo'];
        $this->depositInformation = $cachedData['depositInformation'];
        $this->descriptiveContent = $cachedData['descriptiveContent'] ?? [];
        $this->cancellationPolicies = $cachedData['cancellationPolicies'] ?? [];
        $this->mapperSupplierRepository = $cachedData['mapperSupplierRepository'];
        $this->repoServices = $cachedData['repoServices'];
        $this->basicHotelData = $cachedData['basicHotelData'];
        $this->unifiedRoomCodes = $cachedData['unifiedRoomCodes'];
        $this->roomIdByUnifiedCode = $cachedData['roomIdByUnifiedCode'];
        $this->features = $cachedData['features'];
        $this->rates = $cachedData['rates'];
    }

    protected function initializePricingData(array $query, array $pricingExclusionRules, array $giataIds, string $search_id): void
    {
        $this->search_id = $search_id;
        $this->bookingItems = [];
        $this->checkin = Arr::get($query, 'checkin', Carbon::today()->toDateString());
        $this->checkout = Arr::get($query, 'checkout', Carbon::today()->toDateString());
        $this->occupancy = Arr::get($query, 'occupancy', []);

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
                    $conditionValue = is_array($condition['value'])
                        ? implode(', ', $condition['value'])
                        : ($condition['value'] ?? null);

                    return "{$condition['field']} {$condition['compare']} ".($conditionValue ?? "{$condition['value_from']} - {$condition['value_to']}");
                })->toArray(),
            ];
        })->toArray();

        return [
            'count' => count($listRules),
            'list' => $listRules,
        ];
    }
}
