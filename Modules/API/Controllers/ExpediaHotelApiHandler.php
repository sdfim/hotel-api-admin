<?php

namespace Modules\API\Controllers;

use App\Models\ExpediaContent;
use Exception;
use Illuminate\Http\Request;
use Modules\API\ContentAPI\Controllers\HotelSearchBuilder;
use Modules\API\Suppliers\ExpediaSupplier\ExpediaService;
use Modules\API\Tools\Geography;


class ExpediaHotelApiHandler
{
    private ExpediaService $expediaService;

    protected float|string $current_time;

    private const RESULT_PER_PAGE = 1000;

    private const PAGE = 1;

    private const RATING = 4;

    /**
     * @param  ExpediaService  $expediaService
     */
    public function __construct()
    {
        $this->expediaService = new ExpediaService();
    }

    /**
     * @param  Request  $request
     */
    public function preSearchData(array $filters): ?array
    {
        $timeStart = microtime(true);
        \Log::info('ExpediaHotelApiHandler | preSearchData | start mysql query');

        $resultsPerPage = $filters['results_per_page'] ?? self::RESULT_PER_PAGE;
        $page = $filters['page'] ?? self::PAGE;
        $rating = $filters['rating'] ?? self::RATING;

        try {
            $expedia = new ExpediaContent();

            if (isset($filters['destination'])) {
                $filters['ids'] = $expedia->getIdsByDestinationGiata($filters['destination']);
            } else {
                $geography = new Geography();
                $minMaxCoordinate = $geography->calculateBoundingBox($filters['latitude'], $filters['longitude'], $filters['radius']);
                $filters['ids'] = $expedia->getIdsByCoordinate($minMaxCoordinate);
            }

            $fields = isset($filters['fullList']) ? $expedia->getFullListFields() : $expedia->getShortListFields();
            $query = $expedia->select();

            $searchBuilder = new HotelSearchBuilder($query);
            $results = $searchBuilder->applyFilters($filters);

            $results->leftJoin('expedia_content_slave', 'expedia_content_slave.expedia_property_id', '=', 'expedia_content_main.property_id')
                ->leftJoin('mapper_expedia_giatas', 'mapper_expedia_giatas.expedia_id', '=', 'expedia_content_main.property_id')
                ->where('expedia_content_main.is_active', 1)
                ->whereNotNull('mapper_expedia_giatas.expedia_id')
                ->select(
                    'expedia_content_main.*',
                    'expedia_content_slave.images as images',
                    'expedia_content_slave.amenities as amenities',
                    'mapper_expedia_giatas.expedia_id',
                    'mapper_expedia_giatas.giata_id'
                );

            if (isset($filters['hotel_name'])) {
                $hotelNameArr = explode(' ', $filters['hotel_name']);
                foreach ($hotelNameArr as $hotelName) {
                    $results->where('expedia_content_main.name', 'like', '%'.$hotelName.'%');
                }
            }

            $count = $results->count();

            $results = $results->offset($resultsPerPage * ($page - 1))
                ->limit($resultsPerPage)
                ->cursor();

            $ids = collect($results)->pluck('property_id')->toArray();

            $results = $expedia->dtoDbToResponse($results, $fields);

        } catch (Exception $e) {
            \Log::error('ExpediaHotelApiHandler | preSearchData'.$e->getMessage());

            return null;
        }

        $endTime = microtime(true) - $timeStart;
        \Log::info('ExpediaHotelApiHandler | preSearchData | end mysql query '.$endTime.' seconds');

        return ['ids' => $ids ?? 0, 'results' => $results, 'filters' => $filters ?? null, 'count' => $count ?? 0];
    }

    /**
     * @param  Request  $request
     */
    public function search(array $filters): array
    {
        $preSearchData = $this->preSearchData($filters);
        $results = $preSearchData['results']->toArray() ?? [];

        return ['results' => $results, 'count' => $preSearchData['count']];
    }

    public function price(array $filters): ?array
    {
        $output = [];
        try {
            $preSearchData = $this->preSearchData($filters);
            $filters = $preSearchData['filters'] ?? null;

            // get PriceData from RapidAPI Expedia
            $priceData = $this->expediaService->getExpediaPriceByPropertyIds($preSearchData['ids'], $filters);

            // add price to response
            foreach ($preSearchData['results']->toArray() as $value) {
                if (isset($priceData[$value['property_id']])) {
                    $prices_property = $priceData[$value['property_id']];
                    $output[$value['giata_id']] = [
                        'giata_id' => $value['giata_id'],
                        'hotel_name' => $value['name'],
                    ] + $prices_property;
                }
            }

            return $output ?? null;

        } catch (Exception $e) {
            \Log::error('ExpediaHotelApiHandler '.$e->getMessage());

            return null;
        }
    }

    public function detail(Request $request): object
    {
        $expedia = new ExpediaContent();
        $expedia_id = $expedia->getExpediaIdByGiataId($request->get('property_id'));

        $results = $expedia
            ->leftJoin('expedia_content_slave', 'expedia_content_slave.expedia_property_id', '=', 'expedia_content_main.property_id')
            ->where('property_id', $expedia_id)->get();

        return $expedia->dtoDbToResponse($results, $expedia->getFullListFields());
    }
}
