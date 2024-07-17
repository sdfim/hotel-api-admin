<?php

namespace Modules\API\Controllers\ApiHandlers\ContentSuppliers;

use App\Models\ExpediaContent;
use App\Models\MapperExpediaGiata;
use App\Repositories\ExpediaContentRepository as ExpediaRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\API\ContentAPI\Controllers\HotelSearchBuilder;
use Modules\API\Suppliers\ExpediaSupplier\ExpediaService;
use Modules\API\Suppliers\ExpediaSupplier\RapidClient;
use Modules\API\Tools\Geography;

class ExpediaHotelController
{
    private ExpediaService $expediaService;

    protected float|string $current_time;

    private const RESULT_PER_PAGE = 1000;

    private const PAGE = 1;

    public function __construct()
    {
        $this->expediaService = new ExpediaService();
    }

    public function preSearchData(array $filters, string $initiator): ?array
    {
        $timeStart = microtime(true);

        $resultsPerPage = $filters['results_per_page'] ?? self::RESULT_PER_PAGE;
        $page = $filters['page'] ?? self::PAGE;

        try {
            $expedia = new ExpediaContent();

            if (isset($filters['giata_ids'])) {
                $filters['ids'] = ExpediaRepository::getIdsByGiataIds($filters['giata_ids']);
            } elseif (isset($filters['place'])) {
                $filters['ids'] = ExpediaRepository::getIdsByGiataPlace($filters['place']);
            } elseif (isset($filters['destination'])) {
                $filters['ids'] = ExpediaRepository::getIdsByDestinationGiata($filters['destination']);
            } else {
                $geography = new Geography();
                $minMaxCoordinate = $geography->calculateBoundingBox($filters['latitude'], $filters['longitude'], $filters['radius']);
                $filters['ids'] = ExpediaRepository::getIdsByCoordinate($minMaxCoordinate);
            }

            $fields = isset($filters['fullList']) ? ExpediaContent::getFullListFields() : ExpediaContent::getShortListFields();
            $query = $expedia->select();

            $searchBuilder = new HotelSearchBuilder($query);
            $results = $searchBuilder->applyFilters($filters);

            $mainDB = config('database.connections.mysql.database');

            $selectFields = [
                'expedia_content_main.*',
                'expedia_content_slave.images as images',
                'expedia_content_slave.amenities as amenities',
                $mainDB.'.mapper_expedia_giatas.expedia_id',
                $mainDB.'.mapper_expedia_giatas.giata_id',
            ];

            if ($initiator === 'search') {
                $additionalFields = [
                    'expedia_content_slave.descriptions as descriptions',
                    'expedia_content_slave.checkin as checkin',
                    'expedia_content_slave.checkout as checkout',
                    'expedia_content_slave.fees as fees',
                    'expedia_content_slave.policies as policies',
                ];
                $selectFields = array_merge($selectFields, $additionalFields);
            }

            $results->leftJoin('expedia_content_slave', 'expedia_content_slave.expedia_property_id', '=', 'expedia_content_main.property_id')
                ->leftJoin($mainDB.'.mapper_expedia_giatas', $mainDB.'.mapper_expedia_giatas.expedia_id', '=', 'expedia_content_main.property_id')
                ->where('expedia_content_main.is_active', 1)
                ->whereNotNull($mainDB.'.mapper_expedia_giatas.expedia_id')
                ->select($selectFields);

            if (isset($filters['hotel_name'])) {
                $hotelNameArr = explode(' ', $filters['hotel_name']);
                foreach ($hotelNameArr as $hotelName) {
                    $results->where('expedia_content_main.name', 'like', '%'.$hotelName.'%');
                }
            }

            $count = $results->count();
            $totalPages = ceil($count / $resultsPerPage);

            $results = $results->offset($resultsPerPage * ($page - 1))
                ->limit($resultsPerPage)
                ->cursor();

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

    /**
     * @throws \Throwable
     */
    public function price(array $filters, array $searchInspector): ?array
    {
        try {
            $preSearchData = $this->preSearchData($filters, 'price');
            $filters = $preSearchData['filters'] ?? null;

            if (empty($preSearchData['ids'])) {
                return [
                    'original' => [
                        'request' => [],
                        'response' => [],
                    ],
                    'array' => [],
                    'total_pages' => 0,
                ];
            }

            // get PriceData from RapidAPI Expedia
            $priceData = $this->expediaService->getExpediaPriceByPropertyIds($preSearchData['ids'], $filters, $searchInspector);

            $output = [];
            // add price to response
            foreach ($preSearchData['results']->toArray() as $value) {
                if (isset($priceData['response'][$value['property_id']])) {
                    $prices_property = $priceData['response'][$value['property_id']];
                    $output[$value['giata_id']] = [
                        'giata_id' => $value['giata_id'],
                        'hotel_name' => $value['name'],
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
        /*
        $expedia_id = MapperExpediaGiata::where('giata_id', $request->get('property_id'))
            ->select('expedia_id')
            ->first()?->expedia_id;

        // Detail request example
        if ($expedia_id !== null)
        {
            $response = (new RapidClient())->get("v3/properties/content", [
                'property_id' => $expedia_id,
                'language' => 'en-US',
                'supply_source' => 'expedia',
            ]);

            \Log::debug('### DETAIL RESPONSE ### ');
            $content = json_decode($response->getBody()->getContents(), true);

            \Log::debug(json_encode($content));
        }
        */
        $results = ExpediaRepository::getDetailByGiataId($request->get('property_id'));

        return ExpediaRepository::dtoDbToResponse($results, ExpediaContent::getFullListFields());
    }
}
