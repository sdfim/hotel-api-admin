<?php

namespace Modules\API\Suppliers\HotelTrader\Adapters;

use App\Models\HotelTraderProperty;
use App\Models\Mapping;
use App\Repositories\HotelTraderContentRepository as Repository;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\API\Suppliers\Contracts\Hotel\Search\HotelContentSupplierInterface;
use Modules\API\Suppliers\Contracts\Hotel\Search\HotelContentV1SupplierInterface;
use Modules\API\Suppliers\Contracts\Hotel\Search\HotelPricingSupplierInterface;
use Modules\API\Suppliers\Enums\MappingSuppliersEnum;
use Modules\API\Suppliers\HotelTrader\Client\HotelTraderClient;
use Modules\API\Suppliers\HotelTrader\Transformers\HotelTraderContentDetailTransformer;
use Modules\API\Suppliers\HotelTrader\Transformers\HotelTraderHotelPricingTransformer;
use Modules\API\Tools\Geography;
use Modules\Enums\SupplierNameEnum;

class HotelTraderAdapter implements HotelContentSupplierInterface, HotelContentV1SupplierInterface, HotelPricingSupplierInterface
{
    private const RESULT_PER_PAGE = 5000;

    public function __construct(
        private readonly HotelTraderHotelPricingTransformer $hTraderHotelPricingTransformer,
        private readonly HotelTraderContentDetailTransformer $hotelTraderContentDetailTransformer,
    ) {}

    public function supplier(): SupplierNameEnum
    {
        return SupplierNameEnum::HOTEL_TRADER;
    }

    public function preSearchData(array &$filters, string $initiator = 'search'): ?array
    {
        $timeStart = microtime(true);
        $mainDB = config('database.connections.mysql.database');

        $resultsPerPage = $filters['results_per_page'] ?? self::RESULT_PER_PAGE;

        $cacheKey = 'preSearchData_'.md5(json_encode($filters).$initiator);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $mappings = DB::table($mainDB.'.mappings')
                ->where('supplier', MappingSuppliersEnum::HOTEL_TRADER->value)
                ->whereNotNull('supplier_id')
                ->select('supplier_id as hotel_trader_code', 'giata_id as giata_code')
                ->get()
                ->toArray();

            $mappingsArray = array_column($mappings, 'giata_code', 'hotel_trader_code');

            /** @var HotelTraderProperty $hotelTrader */
            $hotelTrader = app(HotelTraderProperty::class);
            /** @var Geography $geography */
            $geography = app(Geography::class);

            // $filters['ids'] - array of HotelTrader property ids
            // $filters['giata_ids'] - array of Giata ids
            if (isset($filters['giata_ids'])) {
                $filters['ids'] = Repository::getIdsByGiataIds($filters['giata_ids']);
            } elseif (isset($filters['place']) && ! isset($filters['session'])) {
                $filters['ids'] = Repository::getIdsByGiataPlace($filters['place']);
            } elseif (isset($filters['destination'])) {
                $filters['ids'] = Repository::getIdsByDestinationGiata($filters['destination']);
            } elseif (isset($filters['session'])) {
                $geoLocation = $geography->getPlaceDetailById($filters['place'], $filters['session']);

                $minMaxCoordinate = $geography->calculateBoundingBox($geoLocation['latitude'], $geoLocation['longitude'], $filters['radius']);

                $filters['latitude'] = $geoLocation['latitude'];
                $filters['longitude'] = $geoLocation['longitude'];

                $filters['ids'] = Repository::getIdsByCoordinate($minMaxCoordinate);
            } else {
                $minMaxCoordinate = $geography->calculateBoundingBox($filters['latitude'], $filters['longitude'], $filters['radius']);

                $filters['ids'] = Repository::getIdsByCoordinate($minMaxCoordinate);
            }

            // Use the mappings in query logic
            $giataCodes = array_filter(array_map(function ($id) use ($mappingsArray) {
                return $mappingsArray[$id] ?? null;
            }, $filters['ids']));

            $fields = isset($filters['fullList']) ? HotelTraderProperty::getFullListFields() : HotelTraderProperty::getShortListFields();

            $query = $hotelTrader->select();

            if (isset($filters['ids'])) {
                $query->whereIn('propertyId', $filters['ids']);
            }

            if (isset($filters['rating'])) {
                $query->where('starRating', '>=', $filters['rating']);
            }

            if ($initiator === 'price') {
                return Mapping::hotelTrader()->whereIn('supplier_id', $filters['ids'])
                    ->get()
                    ->pluck('giata_id', 'supplier_id')
                    ->toArray();
            } else {
                $selectFields = [
                    'hotel_trader_properties.*',
                    $mainDB.'.mappings.supplier_id',
                    $mainDB.'.mappings.giata_id',
                ];

                $query->leftJoin($mainDB.'.mappings', $mainDB.'.mappings.supplier_id', '=', 'hotel_trader_properties.propertyId')
                    ->whereIn($mainDB.'.mappings.giata_id', $giataCodes)
                    ->select($selectFields);
            }

            if (isset($filters['hotel_name'])) {
                $hotelNameArr = explode(' ', $filters['hotel_name']);
                foreach ($hotelNameArr as $hotelName) {
                    $query->where('hotel_trader_properties.propertyName', 'like', '%'.$hotelName.'%');
                }
            }

            $count = $query->count();
            $totalPages = ceil($count / $resultsPerPage);

            $results = $query->cursor();
            $ids = collect($results)->pluck('property_id')->toArray();

            $results = Repository::dtoDbToResponse($results, $fields);
        } catch (Exception $e) {
            Log::error('HotelTraderAdapter | preSearchData'.$e->getMessage());
            Log::error($e->getTraceAsString());

            return null;
        }

        $endTime = microtime(true) - $timeStart;
        $finalMemoryUsage = memory_get_usage();
        $finalMemoryUsageMB = $finalMemoryUsage / 1024 / 1024;
        Log::info('Final memory usage: '.$finalMemoryUsageMB.' MB');
        Log::info('HotelTraderAdapter | preSearchData | mysql query '.$endTime.' seconds');

        return [
            'giata_ids' => array_values($giataCodes),
            'ids' => $ids ?? [],
            'results' => $results,
            'count' => $count ?? 0,
            'total_pages' => $totalPages,
        ];
    }

    // Content
    public function search(array $filters): array
    {
        $preSearchData = $this->preSearchData($filters, 'search');
        $results = $preSearchData['results']->toArray() ?? [];

        return [
            'results' => $results,
            'count' => $preSearchData['count'],
            'total_pages' => $preSearchData['total_pages'],
        ];
    }

    public function detail(Request $request): object
    {
        $results = Repository::getDetailByGiataId($request->get('property_id'));

        return Repository::dtoDbToResponse($results, HotelTraderProperty::getFullListFields());
    }

    // Content V1
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

    public function getRoomsData(int $giataCode): array
    {
        $roomsData = [];

        $hotelTraderCode = Mapping::where('giata_id', $giataCode)
            ->where('supplier', SupplierNameEnum::HOTEL_TRADER->value)
            ->first()?->supplier_id;
        $hotelTraderData = HotelTraderProperty::where('propertyId', $hotelTraderCode)->first();

        $hotelTraderData = $hotelTraderData ? $hotelTraderData->toArray() : [];
        $rooms = $hotelTraderData['rooms'] ?? [];

        foreach ($rooms as $room) {
            $roomId = $room['roomCode'] ?? $room['displayName'] ?? null;
            $roomsData[] = [
                'id' => $roomId,
                'name' => $room['displayName'] ?? '',
                'descriptions' => [
                    'overview' => $room['shortDesc'] ?? '',
                ],
                'area' => null, // Not provided
                'views' => [], // Not provided
                'bed_groups' => [], // Not provided
                'amenities' => [], // Not provided
                'supplier' => SupplierNameEnum::HOTEL_TRADER->value,
            ];
        }

        return $roomsData;
    }

    // Pricing
    public function price(array &$filters, array $searchInspector, array $hotelData): ?array
    {
        $hotelData = array_flip($hotelData);
        try {
            $hotelIds = array_values($hotelData);

            if (empty($hotelIds)) {
                return [
                    'original' => [
                        'request' => [],
                        'response' => [],
                    ],
                    'array' => [],
                    'total_pages' => 0,
                ];
            }

            /** get PriceData from HotelTrader */
            /* @var HotelTraderClient $hotelTraderClient */
            $hotelTraderClient = app(HotelTraderClient::class);
            $priceDataRaw = $hotelTraderClient->getPriceByPropertyIds($hotelIds, $filters, $searchInspector);
            $priceData = [];

            foreach (Arr::get($priceDataRaw, 'response', []) as $item) {
                if (isset($item['propertyId'])) {
                    $priceData[$item['propertyId']] = $item;
                }
            }

            $result = [];

            if (empty($priceData)) {
                return [
                    'original' => [
                        'request' => [],
                        'response' => [],
                    ],
                    'array' => [],
                    'total_pages' => 0,
                ];
            }

            $output = [];
            foreach ($hotelData as $giata_id => $supplier_id) {
                if (isset($priceData[$supplier_id])) {
                    $prices_property = $priceData[$supplier_id];
                    $output[$giata_id] = [
                        'giata_id' => $giata_id,
                        'hotel_name' => $priceData[$supplier_id]['propertyName'],
                    ] + $prices_property;

                    //                    // Group rooms by roomCode into room_groups
                    if (isset($output[$giata_id]['rooms']) && is_array($output[$giata_id]['rooms'])) {
                        $rooms = [];
                        $i = 1;
                        foreach ($output[$giata_id]['rooms'] as $room) {
                            $roomCode = $room['roomCode'] ?? null;
                            if (! $roomCode) {
                                continue;
                            }
                            // Use roomCode as key
                            if (! isset($rooms[$roomCode])) {
                                $rooms[$roomCode] = [
                                    'id' => $roomCode,
                                    'room_name' => $room['roomName'] ?? '',
                                    // add other static room fields if needed
                                    'rates' => [],
                                ];
                            }
                            // Remove static fields from rate
                            $rate = $room;
                            $rate['rate_ordinal'] = $i;
                            $rooms[$roomCode]['rates'][] = $rate;
                            $i++;
                        }
                        // Re-index rooms numerically
                        $output[$giata_id]['rooms'] = array_values($rooms);
                    }
                }
            }

            logger()->info('HotelTraderAdapter _ price', [
                'hotel_ids' => $hotelIds,
                'filters' => $filters,
                'result' => $result,
            ]);

            return [
                'original' => [
                    'request' => $priceDataRaw['request'] ?? [],
                    'response' => $priceDataRaw['response'] ?? [],
                ],
                'array' => $output,
                'total_pages' => $hotelData['total_pages'] ?? 1,
            ];

        } catch (Exception $e) {
            Log::error('HotelTraderAdapter Exception '.$e);
            Log::error($e->getTraceAsString());

            return [
                'error' => $e->getMessage(),
                'original' => [
                    'request' => $xmlPriceData['request'] ?? '',
                    'response' => isset($xmlPriceData['response']) ? $xmlPriceData['response']->asXML() : '',
                ],
                'array' => [],
                'total_pages' => 0,
            ];
        } catch (GuzzleException $e) {
            Log::error('HotelTraderAdapter GuzzleException '.$e);
            Log::error($e->getTraceAsString());

            return [
                'error' => $e->getMessage(),
                'original' => [
                    'request' => $xmlPriceData['request'] ?? '',
                    'response' => isset($xmlPriceData['response']) ? $xmlPriceData['response']->asXML() : '',
                ],
                'array' => [],
                'total_pages' => 0,
            ];
        }
    }

    /**
     * Processes the raw response from the supplier (received from price),
     * transforms it into a DTO, and applies calculation logic.
     */
    public function processPriceResponse(
        array $rawResponse,
        array $filters,
        string $searchId,
        array $pricingRules,
        array $pricingExclusionRules,
        array $giataIds
    ): array {
        $supplierName = SupplierNameEnum::HOTEL_TRADER->value;

        $dataResponse = [];
        $clientResponse = [];
        $totalPages = [];
        $bookingItems = [];
        $countResponse = 0;
        $countClientResponse = 0;
        $dataOriginal = [];

        $hTraderResponse = $rawResponse;

        $dataResponse[$supplierName] = $hTraderResponse['array'];
        $dataOriginal[$supplierName] = $hTraderResponse['original'];

        $countResponse += count($hTraderResponse['array']);
        $totalPages[$supplierName] = $hTraderResponse['total_pages'] ?? 0;

        $st = microtime(true);
        // Вызов трансформера через инжектированную зависимость
        $hotelGenerator = $this->hTraderHotelPricingTransformer->HotelTraderToHotelResponse(
            $hTraderResponse['array'],
            $filters,
            $searchId,
            $pricingRules,
            $pricingExclusionRules,
            $giataIds
        );

        $clientResponse[$supplierName] = [];
        $count = 0;
        foreach ($hotelGenerator as $count => $hotel) {
            $clientResponse[$supplierName][] = $hotel;
        }

        $bookingItems[$supplierName] = $this->hTraderHotelPricingTransformer->bookingItems ?? [];
        $countClientResponse += $count;

        Log::info('HotelTraderAdapter _ price _ Transformer hTraderToHotelResponse '.(microtime(true) - $st).' seconds');
        unset($hTraderResponse, $hotelGenerator);

        // Формат возвращаемых данных соответствует тому, что ожидает HotelApiHandler
        return [
            'error' => Arr::get($rawResponse, 'error'),
            'dataResponse' => $dataResponse,
            'clientResponse' => $clientResponse,
            'countResponse' => $countResponse,
            'totalPages' => $totalPages,
            'countClientResponse' => $countClientResponse,
            'bookingItems' => $bookingItems,
            'dataOriginal' => $dataOriginal,
        ];
    }
}
