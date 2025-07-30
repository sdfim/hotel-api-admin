<?php

namespace Modules\API\Controllers\ApiHandlers\ContentSuppliers;

use App\Models\HotelTraderContentHotel;
use App\Repositories\HotelTraderContentRepository as Repository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\API\Suppliers\Enums\MappingSuppliersEnum;
use Modules\API\Tools\Geography;

class HotelTraderController implements SupplierControllerInterface
{
    private const RESULT_PER_PAGE = 5000;

    public function preSearchData(array &$filters, string $initiator): ?array
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

            /** @var HotelTraderContentHotel $hotelTrader */
            $hotelTrader = app(HotelTraderContentHotel::class);
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

            $fields = isset($filters['fullList']) ? HotelTraderContentHotel::getFullListFields() : HotelTraderContentHotel::getShortListFields();

            $query = $hotelTrader->select();

            if (isset($filters['ids'])) {
                $query->whereIn('code', $filters['ids']);
            }

            if (isset($filters['rating'])) {
                $query->where('star_rating', '>=', $filters['rating']);
            }

            $selectFields = [
                'hotel_trader_content_hotels.*',
                $mainDB.'.mappings.supplier_id',
                $mainDB.'.mappings.giata_id',
            ];

            $query->leftJoin($mainDB.'.mappings', $mainDB.'.mappings.supplier_id', '=', 'hotel_trader_content_hotels.code')
                ->whereIn($mainDB.'.mappings.giata_id', $giataCodes)
                ->select($selectFields);

            if (isset($filters['hotel_name'])) {
                $hotelNameArr = explode(' ', $filters['hotel_name']);
                foreach ($hotelNameArr as $hotelName) {
                    $query->where('hotel_trader_content_hotels.name', 'like', '%'.$hotelName.'%');
                }
            }

            $count = $query->count();
            $totalPages = ceil($count / $resultsPerPage);

            $results = $query->get();

            $results = Repository::dtoDbToResponse($results, $fields);
        } catch (Exception $e) {
            Log::error('HotelTraderController | preSearchData'.$e->getMessage());
            Log::error($e->getTraceAsString());

            return null;
        }

        $endTime = microtime(true) - $timeStart;
        $finalMemoryUsage = memory_get_usage();
        $finalMemoryUsageMB = $finalMemoryUsage / 1024 / 1024;
        Log::info('Final memory usage: '.$finalMemoryUsageMB.' MB');
        Log::info('HotelTraderController | preSearchData | mysql query '.$endTime.' seconds');

        return [
            'results' => $results,
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

    public function detail(Request $request): object
    {
        $results = Repository::getDetailByGiataId($request->get('property_id'));

        return Repository::dtoDbToResponse($results, HotelTraderContentHotel::getFullListFields());
    }
}
