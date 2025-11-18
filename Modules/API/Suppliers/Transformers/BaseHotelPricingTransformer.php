<?php

namespace Modules\API\Suppliers\Transformers;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Modules\API\Tools\PricingDtoTools;
use Modules\Enums\HotelSaleTypeEnum;
use Modules\HotelContentRepository\Models\Hotel;

class BaseHotelPricingTransformer
{
    private const CACHE_TTL_MINUTES = 1;

    protected array $priorityContentFromSupplierRepo = [];

    // protected array $ultimateAmenities = [];

    protected array $informativeFees = [];

    protected array $depositInformation = [];

    protected array $descriptiveContent = [];

    protected array $cancellationPolicies = [];

    protected array $mapperSupplierRepository = [];

    protected array $rates = [];

    protected array $repoTaxFees = [];

    protected array $repoServices = [];

    protected array $features = [];

    protected array $unifiedRoomCodes = [];

    protected array $roomCodes = [];

    protected array $roomChannels = [];

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

    protected array $commissions = [];

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
        $supplierRepositoryData = Hotel::with('rooms')->whereIn('giata_code', $giataIds)->get();

        // Adjust logic to include 'add', 'edit' with MANUAL_CONTRACT or 'informative' - tmp DISABLED
        $this->informativeFees = $supplierRepositoryData->mapWithKeys(function ($hotel) {
            return [
                $hotel->giata_code => $hotel->product?->feeTaxes
                    ? $hotel->product->feeTaxes
                        ->filter(function ($feeTax) {
                            //                            $isManualContract = $hotel->sale_type === HotelSaleTypeEnum::MANUAL_CONTRACT->value;
                            //                            $isActionTypeMatch = in_array($feeTax->action_type, ['add', 'edit']);
                            //
                            //                            return ($isManualContract && $isActionTypeMatch) || $feeTax->action_type === 'informative';
                            return $feeTax->action_type === 'informative';
                        })
                        ->map(function ($informativeFees) {
                            return [
                                'room_id' => $informativeFees->room_id,
                                'rate_id' => $informativeFees->rate_id,
                                'rate_code' => $informativeFees->rate?->code,
                                'unified_room_code' => $informativeFees->room?->external_code,
                                'supplier_id' => $informativeFees->supplier_id,
                                'description' => $informativeFees->name,
                                'value_type' => $informativeFees->value_type,
                                'apply_type' => $informativeFees->apply_type?->value,
                                'net_value' => $informativeFees->net_value,
                                'rack_value' => $informativeFees->rack_value,
                                // Include filtering and identification attributes so resolvers can apply date/age filters
                                'id' => $informativeFees->id,
                                'name' => $informativeFees->name,
                                'age_from' => $informativeFees->age_from,
                                'age_to' => $informativeFees->age_to,
                                'start_date' => $informativeFees->start_date ?? null,
                                'end_date' => $informativeFees->end_date ?? null,
                                'currency' => $informativeFees->currency ?? null,
                                'collected_by' => $informativeFees->collected_by ?? null,
                                'commissionable' => $informativeFees->commissionable ?? false,
                            ];
                        })->toArray()
                    : [],
            ];
        })->toArray();

        //        $this->ultimateAmenities = $supplierRepositoryData->mapWithKeys(function ($hotel) {
        //            return [
        //                $hotel->giata_code => $hotel->product?->affiliations->map(function ($affiliation) {
        //                    return [
        //                        'rate_code' => $affiliation->rate?->code,
        //                        'unified_room_code' => $affiliation->room?->external_code,
        //                        'start_date' => $affiliation->start_date,
        //                        'end_date' => $affiliation->end_date,
        //                        'amenities' => $affiliation->amenities->map(function ($amenity) {
        //                            $amenityData = [
        //                                'name' => $amenity->amenity->name,
        //                                'consortia' => $amenity->consortia,
        //                                'description' => $amenity->description,
        //                                'is_paid' => $amenity->is_paid,
        //                                'currency' => $amenity->currency,
        //                                'min_night_stay' => $amenity->min_night_stay,
        //                                'max_night_stay' => $amenity->max_night_stay,
        //                                'priority_rooms' => (! empty($amenity->priority_rooms))
        //                                    ? $amenity?->priorityRooms()->pluck('external_code')->toArray() ?? []
        //                                    : [],
        //                            ];
        //                            if ($amenity->is_paid) {
        //                                $amenityData['price'] = $amenity->price;
        //                                $amenityData['apply_type'] = $amenity->apply_type;
        //                            }
        //
        //                            return $amenityData;
        //                        })->toArray(),
        //                    ];
        //                })->toArray(),
        //            ];
        //        })->toArray();

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

                    $content['unified_room_code'] = $descriptiveContent->room?->external_code;

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
                $hotel->giata_code => $hotel?->rates?->mapWithKeys(function ($rate) {
                    $rateArray = $rate->toArray();
                    $rateArray['consortia'] = $rate->consortia->pluck('name')->toArray();

                    return [$rate->code => $rateArray];
                })->toArray() ?? [],
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
                                'bed_groups' => $room->bed_groups,
                            ],
                        ];
                    }

                    return [];
                })->toArray(),
            ];
        })->toArray();

        $this->repoTaxFees = $supplierRepositoryData->mapWithKeys(function ($hotel) {
            return [
                // Logic to include 'add', 'edit' with MANUAL_CONTRACT - tmp DISABLED
                $hotel->giata_code => $hotel->product->feeTaxes->groupBy('action_type')->map(function ($group) {
                    return $group
//                        ->reject(function ($feeTax) use ($hotel) {
//                            $isManualContract = $hotel->sale_type === HotelSaleTypeEnum::MANUAL_CONTRACT->value;
//                            $isActionTypeMatch = in_array($feeTax->action_type, ['add', 'edit']);
//
//                            return $isManualContract && $isActionTypeMatch;
//                        })
                        ->mapWithKeys(function ($feeTax) {
                            $feeTaxData = $feeTax->toArray();
                            $feeTaxData['rate_code'] = null;
                            $feeTaxData['level'] = match (true) {
                                $feeTaxData['rate_id'] !== null => 'rate',
                                ($feeTaxData['rate_id'] === null && $feeTaxData['room_id'] !== null) => 'room',
                                default => 'hotel',
                            };
                            if ($feeTax->rate_id !== null) {
                                $feeTaxData['rate_code'] = $feeTax->rate->code;
                            }
                            $feeTaxData['unified_room_code'] = null;

                            if ($feeTax->room_id !== null && $feeTax->room !== null) {

                                $feeTaxData['unified_room_code'] = $feeTax->room->external_code;
                            }

                            return [$feeTax->id => $feeTaxData];
                        })->toArray();
                })->toArray(),
            ];
        })->toArray();

        $this->basicHotelData = $supplierRepositoryData->mapWithKeys(function ($hotel) {
            return [
                $hotel->giata_code => [
                    'sale_type' => $hotel->sale_type,
                ],
            ];
        })->toArray();

        $this->repoServices = $supplierRepositoryData->mapWithKeys(function ($hotel) {
            if (! $hotel->product->informativeServices) {
                return [
                    $hotel->giata_code => [],
                ];
            }

            return [
                $hotel->giata_code => $hotel->product->informativeServices->map(function ($service) {
                    $feeTaxData = $service->toArray();
                    $feeTaxData['rate_code'] = null;
                    if ($service->rate_id !== null) {
                        $feeTaxData['rate_code'] = $service->rate->code;
                    }
                    $feeTaxData['unified_room_code'] = null;
                    if ($service->room_id !== null && $service?->room) {
                        $feeTaxData['unified_room_code'] = $service->room?->external_code;
                    }

                    return $feeTaxData;
                })->toArray(),
            ];
        })->toArray();

        $this->commissions = $supplierRepositoryData->mapWithKeys(function ($hotel) {
            return [
                $hotel->giata_code => $hotel->product?->travelAgencyCommissions->map(function ($commission) {
                    return [
                        'commission_value' => $commission->commission_value,
                        'commission_value_type' => $commission->commission_value_type,
                        'date_range_start' => $commission->date_range_start,
                        'date_range_end' => $commission->date_range_end,
                        'room_type' => $commission->room_type,
                        'rate_type' => $commission->rate_type,
                        'commission_name' => $commission->commission->name ?? null,
                    ];
                })->toArray() ?? [],
            ];
        })->toArray();

        $this->unifiedRoomCodes = [];
        $this->roomCodes = [];
        foreach ($supplierRepositoryData as $hotel) {
            // Skip hotels that are not on sale if force_on_sale is false
            if (! $this->query['force_on_sale'] && ! $hotel->product->onSale) {
                continue;
            }

            foreach ($hotel->rooms as $room) {
                $supplierCodes = json_decode($room->supplier_codes, true);
                if (! is_array($supplierCodes)) {
                    $supplierCodes = [];
                }
                foreach ($supplierCodes as $codeData) {
                    $supplier = $codeData['supplier'] ?? null;
                    $code = $codeData['code'] ?? null;
                    if ($supplier && $code) {
                        $this->roomCodes[$supplier][$hotel->giata_code][$room->id] = $room->external_code;
                        $this->unifiedRoomCodes[$supplier][$hotel->giata_code][$code] = $room->external_code;
                    }
                }
            }
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
        }
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
            //            'ultimateAmenities' => $this->ultimateAmenities,
            'depositInformation' => $this->depositInformation,
            'descriptiveContent' => $this->descriptiveContent,
            'cancellationPolicies' => $this->cancellationPolicies,
            'mapperSupplierRepository' => $this->mapperSupplierRepository,
            'repoTaxFees' => $this->repoTaxFees,
            'informativeFees' => $this->informativeFees,
            'repoServices' => $this->repoServices,
            'basicHotelData' => $this->basicHotelData,
            'unifiedRoomCodes' => $this->unifiedRoomCodes,
            'roomCodes' => $this->roomCodes,
            'roomIdByUnifiedCode' => $this->roomIdByUnifiedCode,
            'features' => $this->features,
            'rates' => $this->rates,
            'commissions' => $this->commissions,
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
        //        $this->ultimateAmenities = $cachedData['ultimateAmenities'];
        $this->depositInformation = $cachedData['depositInformation'];
        $this->descriptiveContent = $cachedData['descriptiveContent'] ?? [];
        $this->cancellationPolicies = $cachedData['cancellationPolicies'] ?? [];
        $this->mapperSupplierRepository = $cachedData['mapperSupplierRepository'];
        $this->repoTaxFees = $cachedData['repoTaxFees'];
        $this->informativeFees = $cachedData['informativeFees'];
        $this->repoServices = $cachedData['repoServices'];
        $this->basicHotelData = $cachedData['basicHotelData'];
        $this->unifiedRoomCodes = $cachedData['unifiedRoomCodes'];
        $this->roomCodes = $cachedData['roomCodes'];
        $this->roomIdByUnifiedCode = $cachedData['roomIdByUnifiedCode'];
        $this->features = $cachedData['features'];
        $this->rates = $cachedData['rates'];
        $this->commissions = $cachedData['commissions'];
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
