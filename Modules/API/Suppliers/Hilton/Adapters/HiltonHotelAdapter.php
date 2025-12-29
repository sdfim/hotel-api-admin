<?php

namespace Modules\API\Suppliers\Hilton\Adapters;

use App\Models\HiltonProperty;
use App\Models\Mapping;
use App\Repositories\HiltonContentRepository as HiltonRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\API\Suppliers\Contracts\Hotel\Search\HotelContentSupplierInterface;
use Modules\API\Suppliers\Contracts\Hotel\Search\HotelContentV1SupplierInterface;
use Modules\API\Suppliers\Enums\MappingSuppliersEnum;
use Modules\API\Suppliers\Hilton\Transformers\HiltonHotelContentDetailTransformer;
use Modules\API\Tools\Geography;
use Modules\Enums\SupplierNameEnum;

class HiltonHotelAdapter implements HotelContentV1SupplierInterface, HotelContentSupplierInterface
{
    private const RESULT_PER_PAGE = 5000;

    public function __construct(
        protected readonly HiltonHotelContentDetailTransformer $hiltonHotelContentDetailTransformer,
    ) {}

    public function supplier(): SupplierNameEnum
    {
        return SupplierNameEnum::HILTON;
    }

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
                ->where('supplier', MappingSuppliersEnum::HILTON->value)
                ->whereNotNull('supplier_id')
                ->select('supplier_id as hilton_code', 'giata_id as giata_code')
                ->get()
                ->toArray();

            $mappingsArray = array_column($mappings, 'giata_code', 'hilton_code');

            /** @var HiltonProperty $hilton */
            $hilton = app(HiltonProperty::class);
            /** @var Geography $geography */
            $geography = app(Geography::class);

            // $filters['ids'] - array of Hilton property ids
            // $filters['giata_ids'] - array of Giata ids
            if (isset($filters['giata_ids'])) {
                $filters['ids'] = HiltonRepository::getIdsByGiataIds($filters['giata_ids']);
            } elseif (isset($filters['place']) && ! isset($filters['session'])) {
                $filters['ids'] = HiltonRepository::getIdsByGiataPlace($filters['place']);
            } elseif (isset($filters['destination'])) {
                $filters['ids'] = HiltonRepository::getIdsByDestinationGiata($filters['destination']);
            } elseif (isset($filters['session'])) {
                $geoLocation = $geography->getPlaceDetailById($filters['place'], $filters['session']);

                $minMaxCoordinate = $geography->calculateBoundingBox($geoLocation['latitude'], $geoLocation['longitude'], $filters['radius']);

                $filters['latitude'] = $geoLocation['latitude'];
                $filters['longitude'] = $geoLocation['longitude'];

                $filters['ids'] = HiltonRepository::getIdsByCoordinate($minMaxCoordinate);
            } else {
                $minMaxCoordinate = $geography->calculateBoundingBox($filters['latitude'], $filters['longitude'], $filters['radius']);

                $filters['ids'] = HiltonRepository::getIdsByCoordinate($minMaxCoordinate);
            }

            // Use the mappings in query logic
            $giataCodes = array_filter(array_map(function ($id) use ($mappingsArray) {
                return $mappingsArray[$id] ?? null;
            }, $filters['ids']));

            $fields = isset($filters['fullList']) ? HiltonProperty::getFullListFields() : HiltonProperty::getShortListFields();

            $query = $hilton->select();

            if (isset($filters['ids'])) {
                $query->whereIn('prop_code', $filters['ids']);
            }

            if (isset($filters['rating'])) {
                $query->where('star_rating', '>=', $filters['rating']);
            }

            $selectFields = [
                'hilton_properties.*',
                $mainDB.'.mappings.supplier_id',
                $mainDB.'.mappings.giata_id',
            ];

            $query->leftJoin($mainDB.'.mappings', $mainDB.'.mappings.supplier_id', '=', 'hilton_properties.prop_code')
                ->whereIn($mainDB.'.mappings.giata_id', $giataCodes)
                ->select($selectFields);

            if (isset($filters['hotel_name'])) {
                $hotelNameArr = explode(' ', $filters['hotel_name']);
                foreach ($hotelNameArr as $hotelName) {
                    $query->where('hilton_properties.name', 'like', '%'.$hotelName.'%');
                }
            }

            $count = $query->count();
            $totalPages = ceil($count / $resultsPerPage);

            $results = $query->get();

            $results = HiltonRepository::dtoDbToResponse($results, $fields);
        } catch (Exception $e) {
            Log::error('HiltonHotelApiHandler | preSearchData'.$e->getMessage());
            Log::error($e->getTraceAsString());

            return null;
        }

        $endTime = microtime(true) - $timeStart;
        $finalMemoryUsage = memory_get_usage();
        $finalMemoryUsageMB = $finalMemoryUsage / 1024 / 1024;
        Log::info('Final memory usage: '.$finalMemoryUsageMB.' MB');
        Log::info('HiltonHotelApiHandler | preSearchData | mysql query '.$endTime.' seconds');

        return [
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
        $results = HiltonRepository::getDetailByGiataId($request->get('property_id'));

        return HiltonRepository::dtoDbToResponse($results, HiltonProperty::getFullListFields());
    }

    // Content V1
    public function getResults(array $giataCodes): array
    {
        $hiltonCodes = Mapping::hilton()->whereIn('giata_id', $giataCodes)->pluck('giata_id', 'supplier_id')->toArray();
        $resultsHilton = HiltonProperty::whereIn('prop_code', array_keys($hiltonCodes))->get();

        $results = [];
        foreach ($resultsHilton as $item) {
            $giataId = $hiltonCodes[$item->prop_code];
            $contentDetailResponse = $this->hiltonHotelContentDetailTransformer->HiltonToContentDetailResponse($item, $giataId);
            $results = array_merge($results, $contentDetailResponse);
        }

        return $results;
    }

    public function getRoomsData(int $giataCode): array
    {
        $roomsData = [];
        $hiltonCode = Mapping::where('giata_id', $giataCode)
            ->where('supplier', SupplierNameEnum::HILTON->value)
            ->first()?->supplier_id;
        $hiltonData = HiltonProperty::where('prop_code', $hiltonCode)->first();
        $hiltonData = $hiltonData ? $hiltonData->toArray() : [];

        // Example transformation, adjust according to your IcePortalPropertyAsset structure
        $rooms = $hiltonData['guest_room_descriptions'] ?? [];

        foreach ($rooms as $room) {
            $roomId = $room['roomTypeCode'] ?? null;
            $roomsData[] = [
                'id' => $roomId,
                'name' => $room['shortDescription'],
                'descriptions' => $room['enhancedDescription'],
                'area' => $room['area'] ?? null,
                'views' => '',
                'bed_groups' => $room['bedClass'] ?? [],
                'amenities' => $room['roomAmenities'] ?? [],
                'supplier' => SupplierNameEnum::HILTON->value,
                'roomsOccupancy' => $room['maxOccupancy'],
            ];
        }

        return $roomsData;
    }
}
