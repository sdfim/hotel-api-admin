<?php

namespace Modules\API\Suppliers\Base\Adapters;

use App\Repositories\GeneralMappingRepository;
use Illuminate\Support\Facades\Log;
use Modules\API\Suppliers\Enums\MappingSuppliersEnum;
use Modules\API\Tools\Geography;

class BaseHotelAdapter extends BaseEnrichmentRoomCombinations
{
    private const RESULT_PER_PAGE = 1000;

    private const PAGE = 1;

    public function preSearchData(MappingSuppliersEnum $supplier, array &$filters, string $initiator = 'price'): ?array
    {
        if ($initiator != 'price') {
            return null;
        }

        $geography = app(Geography::class);
        $repository = app(GeneralMappingRepository::class);

        $timeStart = microtime(true);

        $limit = $filters['results_per_page'] ?? self::RESULT_PER_PAGE;
        $offset = $filters['page'] ?? self::PAGE;

        if (isset($filters['giata_ids'])) {
            $ids = $repository->getIdsByGiataIds($supplier, $filters['giata_ids'], $limit, $offset);
        } elseif (isset($filters['place']) && ! isset($filters['session'])) {
            $ids = $repository->getIdsByGiataPlace($supplier, $filters['place'], $limit, $offset);
        } elseif (isset($filters['destination'])) {
            $ids = $repository->getIdsByDestinationGiata($supplier, $filters['destination'], $limit, $offset);
        } elseif (isset($filters['session'])) {
            $geoLocationTime = microtime(true);
            $geoLocation = $this->geography->getPlaceDetailById($filters['place'], $filters['session']);
            $endTime = microtime(true) - $geoLocationTime;
            Log::info('HbsiHotelAdapter | preSearchData | geoLocation '.$endTime.' seconds');

            $coordinateTime = microtime(true);
            $minMaxCoordinate = $this->geography->calculateBoundingBox($geoLocation['latitude'], $geoLocation['longitude'], $filters['radius']);
            $endTime = microtime(true) - $coordinateTime;
            Log::info('HbsiHotelAdapter | preSearchData | minMaxCoordinate '.$endTime.' seconds');

            $filters['latitude'] = $geoLocation['latitude'];
            $filters['longitude'] = $geoLocation['longitude'];

            $idsTime = microtime(true);
            $ids = $repository->getIdsByCoordinate($supplier, $minMaxCoordinate, $limit, $offset, $filters);
            $endTime = microtime(true) - $idsTime;
            Log::info('HbsiHotelAdapter | preSearchData | ids '.$endTime.' seconds');
        } else {
            $minMaxCoordinate = $geography->calculateBoundingBox($filters['latitude'], $filters['longitude'], $filters['radius']);
            $ids = $repository->getIdsByCoordinate($supplier, $minMaxCoordinate, $limit, $offset, $filters);
        }

        $endTime = microtime(true) - $timeStart;
        Log::info('HbsiHotelAdapter | preSearchData | mysql query '.$endTime.' seconds');

        return array_column($ids['data'], 'giata', $supplier->value);
    }
}
