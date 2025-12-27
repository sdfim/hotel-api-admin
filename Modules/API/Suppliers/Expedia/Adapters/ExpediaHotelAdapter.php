<?php

namespace Modules\API\Suppliers\Expedia\Adapters;

use App\Models\ExpediaContent;
use App\Repositories\ExpediaContentRepository as ExpediaRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\API\ContentAPI\Controllers\HotelSearchBuilder;
use Modules\API\Services\MappingCacheService;
use Modules\API\Suppliers\Contracts\Hotel\Search\HotelContentSupplierInterface;
use Modules\API\Suppliers\Contracts\Hotel\Search\HotelPricingSupplierInterface;
use Modules\API\Suppliers\Expedia\Client\ExpediaService;
use Modules\API\Suppliers\Expedia\Transformers\ExpediaHotelPricingTransformer;
use Modules\API\Tools\Geography;
use Modules\Enums\SupplierNameEnum;

class ExpediaHotelAdapter implements HotelContentSupplierInterface, HotelPricingSupplierInterface
{
    protected float|string $current_time;

    private const RESULT_PER_PAGE = 5000;

    private const PAGE = 1;

    public function __construct(
        private ExpediaService $expediaService,
        private MappingCacheService $mappingCacheService,
        private readonly ExpediaHotelPricingTransformer $expediaHotelPricingTransformer,
    ) {}

    public function preSearchData(array &$filters, string $initiator): ?array
    {
        $timeStart = microtime(true);
        $mainDB = config('database.connections.mysql.database');

        $resultsPerPage = $filters['results_per_page'] ?? self::RESULT_PER_PAGE;
        $page = $filters['page'] ?? self::PAGE;

        $cacheKey = 'preSearchData_'.md5(json_encode($filters).$initiator);
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $mappings = $this->mappingCacheService->getMappingsExpediaHashMap($mainDB);

            $expedia = new ExpediaContent;
            $geography = new Geography;

            // $filters['ids'] - array of Expedia property ids
            // $filters['giata_ids'] - array of Giata ids
            if (isset($filters['giata_ids'])) {
                $filters['ids'] = ExpediaRepository::getIdsByGiataIds($filters['giata_ids']);
            } elseif (isset($filters['place']) && ! isset($filters['session'])) {
                $filters['ids'] = ExpediaRepository::getIdsByGiataPlace($filters['place']);
            } elseif (isset($filters['destination'])) {
                $filters['ids'] = ExpediaRepository::getIdsByDestinationGiata($filters['destination']);
            } elseif (isset($filters['session'])) {
                $geoLocation = $geography->getPlaceDetailById($filters['place'], $filters['session']);
                $minMaxCoordinate = $geography->calculateBoundingBox($geoLocation['latitude'], $geoLocation['longitude'], $filters['radius']);
                $filters['latitude'] = $geoLocation['latitude'];
                $filters['longitude'] = $geoLocation['longitude'];

                $filters['ids'] = ExpediaRepository::getIdsByCoordinate($minMaxCoordinate);
            } else {
                $minMaxCoordinate = $geography->calculateBoundingBox($filters['latitude'], $filters['longitude'], $filters['radius']);
                $filters['ids'] = ExpediaRepository::getIdsByCoordinate($minMaxCoordinate);
            }

            // Use the mappings in query logic
            $giataCodes = array_filter(array_map(function ($id) use ($mappings) {
                return $mappings[$id] ?? null;
            }, $filters['ids']));

            $fields = isset($filters['fullList']) ? ExpediaContent::getFullListFields() : ExpediaContent::getShortListFields();
            $query = $expedia->select();

            $searchBuilder = new HotelSearchBuilder($query);
            $queryBuilder = $searchBuilder->applyFilters($filters);

            if ($initiator === 'price') {
                $expediaQuery = ExpediaContent::query();
                if (! empty($filters['ids'])) {
                    $expediaQuery->whereIn('property_id', $filters['ids']);
                }
                if (! empty($filters['rating'])) {
                    $expediaQuery->where('rating', '>=', $filters['rating']);
                }
                $expediaQuery->with(['mapperGiataExpedia' => function ($query) {
                    $query->select('supplier_id', 'giata_id');
                }]);
                $expediaResults = $expediaQuery->get();
                $result = [];
                foreach ($expediaResults as $expedia) {
                    foreach ($expedia->mapperGiataExpedia as $mapping) {
                        $result[$mapping->giata_id] = $mapping->supplier_id;
                    }
                }

                return array_flip($result);
            } else {
                $selectFields = [
                    'expedia_content_main.*',
                    'expedia_content_slave.images as images',
                    'expedia_content_slave.amenities as amenities',
                    $mainDB.'.mappings.supplier_id',
                    $mainDB.'.mappings.giata_id',
                ];

                if ($initiator === 'search') {
                    $additionalFields = [
                        'expedia_content_slave.images as images',
                        'expedia_content_slave.amenities as amenities',
                        'expedia_content_slave.descriptions as descriptions',
                        'expedia_content_slave.checkin as checkin',
                        'expedia_content_slave.checkout as checkout',
                        'expedia_content_slave.fees as fees',
                        'expedia_content_slave.policies as policies',
                        'expedia_content_slave.statistics as statistics',
                    ];
                    $selectFields = array_merge($selectFields, $additionalFields);
                }

                $queryBuilder->leftJoin('expedia_content_slave', 'expedia_content_slave.expedia_property_id', '=', 'expedia_content_main.property_id')
                    ->leftJoin($mainDB.'.mappings', $mainDB.'.mappings.supplier_id', '=', 'expedia_content_main.property_id')
                    ->where('expedia_content_main.is_active', 1)
//                ->where($mainDB.'.mappings.supplier', MappingSuppliersEnum::Expedia->value)
//                ->whereNotNull($mainDB.'.mappings.supplier_id')
                    ->whereIn($mainDB.'.mappings.giata_id', $giataCodes)
                    ->select($selectFields);

                if (isset($filters['hotel_name'])) {
                    $hotelNameArr = explode(' ', $filters['hotel_name']);
                    foreach ($hotelNameArr as $hotelName) {
                        $queryBuilder->where('expedia_content_main.name', 'like', '%'.$hotelName.'%');
                    }
                }
            }

            $count = $queryBuilder->count();
            $totalPages = ceil($count / $resultsPerPage);

            $results = $queryBuilder->cursor();
            $ids = collect($results)->pluck('property_id')->toArray();

            $results = ExpediaRepository::dtoDbToResponse($results, $fields);

        } catch (Exception $e) {
            Log::error('ExpediaHotelApiHandler | preSearchData'.$e->getMessage());
            Log::error($e->getTraceAsString());

            return null;
        }

        $endTime = microtime(true) - $timeStart;
        Log::info('ExpediaHotelApiHandler | preSearchData | mysql query '.$endTime.' seconds');

        return [
            'giata_ids' => array_values($giataCodes),
            'ids' => $ids ?? 0,
            'results' => $results,
            'filters' => $filters ?? null,
            'count' => $count ?? 0,
            'total_pages' => $totalPages,
        ];
    }

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

    public function price(array &$filters, array $searchInspector, array $preSearchData): ?array
    {
        $preSearchData = array_flip($preSearchData);

        try {
            if (empty($preSearchData)) {
                return [
                    'original' => [
                        'request' => [],
                        'response' => [],
                    ],
                    'array' => [],
                    'total_pages' => 0,
                ];
            }

            // get PriceData from RapidAPI Expedia using filtered IDs
            $priceData = $this->expediaService->getExpediaPriceByPropertyIds($preSearchData, $filters, $searchInspector);

            $output = [];
            // add price to response
            foreach ($preSearchData as $giata_id => $supplier_id) {
                if (isset($priceData['response'][$supplier_id])) {
                    $prices_property = $priceData['response'][$supplier_id];
                    $output[$giata_id] = [
                        'giata_id' => $giata_id,
                        //                        'hotel_name' => $priceData[$supplier_id]['propertyName'],
                    ] + $prices_property;
                }
            }

            return [
                'original' => [
                    'request' => $priceData['request'],
                    'response' => $priceData['response'],
                ],
                'array' => $output,
                'total_pages' => $preSearchData['total_pages'] ?? 0,
            ];

        } catch (Exception $e) {
            Log::error('ExpediaHotelApiHandler '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    public function detail(Request $request): object
    {
        if ($request->has('property_ids')) {
            $propertyIds = explode(',', $request->get('property_ids'));
        } else {
            $propertyIds = [$request->get('property_id')];
        }

        $results = ExpediaRepository::getDetailByGiataIds($propertyIds);

        return ExpediaRepository::dtoDbToResponse($results, ExpediaContent::getFullListFields());
    }

    public function processPriceResponse(
        array $rawResponse,
        array $filters,
        string $searchId,
        array $pricingRules,
        array $pricingExclusionRules,
        array $giataIds
    ): array {
        $supplierName = SupplierNameEnum::EXPEDIA->value;

        $dataResponse = [];
        $clientResponse = [];
        $totalPages = [];
        $bookingItems = [];
        $countResponse = 0;
        $countClientResponse = 0;
        $dataOriginal = [];

        $expediaResponse = $rawResponse;

        $dataResponse[$supplierName] = $expediaResponse['array'];
        $dataOriginal[$supplierName] = $expediaResponse['original'];

        $countResponse += count($expediaResponse['array']);
        $totalPages[$supplierName] = $expediaResponse['total_pages'] ?? 0;

        $st = microtime(true);
        // Используем инжектированный трансформер
        $hotelGenerator = $this->expediaHotelPricingTransformer->ExpediaToHotelResponse(
            $expediaResponse['array'],
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

        $bookingItems[$supplierName] = $this->expediaHotelPricingTransformer->bookingItems ?? [];
        $countClientResponse += $count;

        Log::info('ExpediaHotelAdapter _ price _ Transformer ExpediaToHotelResponse '.(microtime(true) - $st).' seconds');
        unset($expediaResponse, $hotelGenerator);

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
