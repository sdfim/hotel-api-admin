<?php

namespace Modules\API\Controllers\ApiHandlers;

use App\Models\ExpediaContent;
use App\Models\GeneralConfiguration;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\API\Controllers\ApiHandlers\ContentSuppliers\ExpediaHotelController;
use Modules\API\Controllers\ApiHandlers\ContentSuppliers\IcePortalHotelController;
use Modules\API\Services\DetailDataTransformer;
use Modules\API\Services\MappingCacheService;
use Modules\API\Suppliers\Transformers\Expedia\ExpediaHotelContentDetailTransformer;
use Modules\API\Suppliers\Transformers\Expedia\ExpediaHotelContentTransformer;
use Modules\API\Suppliers\Transformers\IcePortal\IcePortalHotelContentDetailTransformer;
use Modules\API\Suppliers\Transformers\IcePortal\IcePortalHotelContentTransformer;
use Modules\Enums\SupplierNameEnum;
use Modules\HotelContentRepository\Models\Hotel;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class HotelApiHandlerV1 extends HotelApiHandler
{
    public function __construct(
        private readonly ExpediaHotelContentDetailTransformer $ExpediaHotelContentDetailDto,
        private readonly DetailDataTransformer $dataTransformer,
        private readonly MappingCacheService $mappingCacheService,
        private readonly ExpediaHotelController $expedia,
        private readonly IcePortalHotelController $icePortal,
        private readonly ExpediaHotelContentTransformer $expediaHotelContentDto,
        private readonly IcePortalHotelContentTransformer $icePortalHotelContentDto,
        private readonly IcePortalHotelContentDetailTransformer $icePortalHotelContentDetailDto,
    ) {}

    public function search(Request $request): JsonResponse
    {
        try {
            $keyContent = $this->generateCacheKey($request);

            if (Cache::has($keyContent.':dataResponse')) {
                $contentResults = Cache::get($keyContent.':dataResponse');
            } else {
                $contentResults = $this->fetchContentResults($request);
                Cache::put($keyContent.':dataResponse', $contentResults, now()->addMinutes(self::TTL));
            }

            $page = $request->input('page', 1);
            $resultsPerPage = $request->input('results_per_page', 1000);
            $paginatedResults = $this->sortAndPaginate($contentResults, $page, $resultsPerPage);

            return $this->sendResponse([
                'query' => $request->all(),
                'total_count' => count($contentResults),
                'page' => $page,
                'results_per_page' => $resultsPerPage,
                'results' => $paginatedResults,
            ], 'success');
        } catch (Exception|NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            Log::error('HotelApiHandler '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return $this->sendError($e->getMessage(), 'failed');
        }
    }

    public function detail(Request $request): JsonResponse
    {
        try {
            $keyDetail = $this->generateCacheKey($request);

            if (Cache::has($keyDetail.':dataResponse')) {
                $detailResults = Cache::get($keyDetail.':dataResponse');
            } else {
                $detailResults = $this->fetchDetailResults($request);
                Cache::put($keyDetail.':dataResponse', $detailResults, now()->addMinutes(self::TTL));
            }

            return $this->sendResponse(['results' => $detailResults], 'success');
        } catch (Exception|NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            Log::error('HotelApiHandler '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return $this->sendError($e->getMessage(), 'failed');
        }
    }

    private function sortAndPaginate(array $contentResults, int $page, int $resultsPerPage): array
    {
        usort($contentResults, function ($a, $b) {
            return $b['weight'] <=> $a['weight'];
        });

        $offset = ($page - 1) * $resultsPerPage;

        return array_slice($contentResults, $offset, $resultsPerPage);
    }

    private function generateCacheKey(Request $request): string
    {
        $queryParams = $request->except(['page', 'results_per_page']);

        return $request->type.':contentDetail:'.http_build_query(Arr::dot($queryParams));
    }

    private function fetchContentResults(Request $request): array
    {
        $resultsExpedia = Arr::get($this->expedia->search($request->all()), 'results', []);
        $resultsIcePortal = Arr::get($this->icePortal->search($request->all()), 'results', []);

        $giataCodes = $this->getGiataCodesByContent($resultsExpedia, $resultsIcePortal);
        $contentSource = $this->dataTransformer->initializeContentSource($giataCodes);
        $repoData = $this->getRepoData($giataCodes);
        $structureSource = $this->dataTransformer->buildStructureSource($repoData, $contentSource);

        $resultsExpediaDto = $this->expediaHotelContentDto->SupplierToContentSearchResponse($resultsExpedia);
        $resultsIcePortalDto = $this->icePortalHotelContentDto->SupplierToContentSearchResponse($resultsIcePortal);

        return $this->combineContentResults($resultsExpediaDto, $resultsIcePortalDto, $structureSource, $repoData, $giataCodes);
    }

    private function fetchDetailResults(Request $request): array
    {
        $giataCodes = $this->getGiataCodes($request);
        $contentSource = $this->dataTransformer->initializeContentSource($giataCodes);
        $repoData = $this->getRepoData($giataCodes);
        $structureSource = $this->dataTransformer->buildStructureSource($repoData, $contentSource);
        $resultsExpedia = $this->getExpediaResults($giataCodes);
        $resultsIcePortal = $this->getIcePortalResults($giataCodes, $structureSource, $repoData);

        return $this->combineDetailResults($resultsExpedia, $resultsIcePortal, $structureSource, $repoData, $giataCodes);
    }

    private function getSupplierNames(): array
    {
        return explode(', ', GeneralConfiguration::pluck('content_supplier')->toArray()[0]);
    }

    private function getGiataCodes(Request $request): array
    {
        return $request->input('property_ids')
            ? explode(',', str_replace(' ', '', $request->input('property_ids')))
            : [$request->input('property_id')];
    }

    private function getGiataCodesByContent(array $resultsExpedia, array $resultsIcePortal): array
    {
        return array_merge(
            array_column($resultsExpedia, 'giata_id'),
            array_column($resultsIcePortal, 'giata_id')
        );
    }

    private function getRepoData(array $giataCodes): ?Collection
    {
        $hotels = Hotel::with(['product' => function ($query) {
            $query->where('onSale', 1);
        }])->whereIn('giata_code', $giataCodes)->get();

        foreach ($hotels as $hotel) {
            if (! $hotel->product) {
                unset($hotels[$hotel->giata_code]);
            }
        }

        return $hotels;
    }

    private function getExpediaResults(array $giataCodes): array
    {
        $resultsExpedia = [];
        $mappingsExpedia = $this->mappingCacheService->getMappingsExpediaHashMap();
        $expediaCodes = $this->getExpediaCodes($giataCodes, $mappingsExpedia);
        $expediaData = $this->getExpediaData($expediaCodes);

        foreach ($expediaData as $item) {
            if (! isset($item->expediaSlave)) {
                continue;
            }
            foreach ($item->expediaSlave->getAttributes() as $key => $value) {
                if (is_string($value)) {
                    $value = json_decode($value, true);
                }
                $item->$key = $value;
            }
            $contentDetailResponse = $this->ExpediaHotelContentDetailDto->ExpediaArrayToContentDetailResponse($item->toArray(), $mappingsExpedia[$item->property_id]);
            $resultsExpedia = array_merge($resultsExpedia, $contentDetailResponse);
        }

        return $resultsExpedia;
    }

    private function getIcePortalResults(array $giataCodes, array $structureSource, $repoData): array
    {
        $resultsIcePortal = $this->icePortal->details($giataCodes);

        $results = [];
        foreach ($resultsIcePortal as $giataId => $item) {
            $contentDetailResponse = $this->icePortalHotelContentDetailDto
                ->HbsiToContentDetailResponseWithAssets($item, $giataId, $item['assets']);
            $results = array_merge($results, $contentDetailResponse);
        }

        return $results;
    }

    private function getExpediaCodes(array $giataCodes, array $mappingsExpedia): array
    {
        $expediaCodes = [];
        foreach ($giataCodes as $giataCode) {
            $expediaCode = array_search($giataCode, $mappingsExpedia);
            if ($expediaCode !== false) {
                $expediaCodes[] = $expediaCode;
            }
        }

        return $expediaCodes;
    }

    private function getExpediaData(array $expediaCodes)
    {
        return ExpediaContent::with('expediaSlave')
            ->whereIn('property_id', $expediaCodes)
            ->get();
    }

    private function combineDetailResults(array $resultsExpedia, array $resultsIcePortal, array $structureSource, $repoData, array $giataCodes): array
    {
        $rooms = $this->getRooms($repoData, $giataCodes);
        $roomMappers = $this->getRoomMappers($rooms);

        $existingExpediaGiataIds = array_column($resultsExpedia, 'giata_hotel_code');
        $filteredResultsIcePortal = $this->filterResultsIcePortal($resultsIcePortal, $existingExpediaGiataIds);

        $detailResults = array_merge($resultsExpedia, $filteredResultsIcePortal);
        $romsImagesData = $this->mergeRooms($detailResults, $resultsExpedia, $resultsIcePortal, $roomMappers);

        $missingGiataCodes = $this->getMissingGiataCodes($giataCodes);
        $detailResults = $this->addMissingGiataCodes($detailResults, $missingGiataCodes);

        return $this->updateDetailResults($detailResults, $structureSource, $repoData, $resultsIcePortal, $romsImagesData);
    }

    private function getRooms($repoData, array $giataCodes): array
    {
        return $repoData->whereIn('giata_code', $giataCodes)->pluck('rooms', 'giata_code')->toArray();
    }

    private function getRoomMappers(array $rooms): array
    {
        $roomMappers = [];
        foreach ($rooms as $giataCode => $roomArray) {
            foreach ($roomArray as $room) {
                $supplierCodes = json_decode($room['supplier_codes'], true);
                if (! $supplierCodes) {
                    continue;
                }
                foreach ($supplierCodes as $supplier) {
                    $mapper[$supplier['supplier']] = $supplier['code'];
                    $mapper['external_code'] = $room['hbsi_data_mapped_name'];
                }
                $roomMappers[$giataCode][] = $mapper;
            }
        }

        return $roomMappers;
    }

    private function filterResultsIcePortal(array $resultsIcePortal, array $existingExpediaGiataIds): array
    {
        return array_filter($resultsIcePortal, function ($item) use ($existingExpediaGiataIds) {
            return ! in_array($item['giata_hotel_code'], $existingExpediaGiataIds);
        });
    }

    private function mergeRooms(array &$detailResults, array $resultsExpedia, array $resultsIcePortal, array $roomMappers): array
    {
        $romsImagesData = [];
        foreach ($detailResults as &$item) {
            $giataCode = $item['giata_hotel_code'];
            $expediaKey = array_search($giataCode, array_column($resultsExpedia, 'giata_hotel_code'));
            $icePortalKey = array_search($giataCode, array_column($resultsIcePortal, 'giata_hotel_code'));

            $expediaRooms = $expediaKey !== false ? $resultsExpedia[$expediaKey]['rooms'] : [];
            $icePortalRooms = $icePortalKey !== false ? $resultsIcePortal[$icePortalKey]['rooms'] : [];

            $mergedRooms = [];
            foreach ($expediaRooms as $expediaRoom) {
                $expediaRoomId = $expediaRoom['supplier_room_id'];
                $foundInMapper = false;

                if (! isset($roomMappers[$giataCode])) {
                    $mergedRooms[] = $expediaRoom;

                    continue;
                }

                foreach ($roomMappers[$giataCode] as $mapper) {
                    $externalCode = $mapper['external_code'];

                    if (in_array($expediaRoomId, $mapper)) {
                        $romsImagesData[$giataCode][$externalCode][SupplierNameEnum::EXPEDIA->value] = $expediaRoom['images'];
                        $foundInMapper = true;
                        $icePortalRoomId = Arr::get($mapper, SupplierNameEnum::ICE_PORTAL->value);
                        if (! $icePortalRoomId) {
                            $expediaRoom['supplier_codes'] = $mapper;
                            $mergedRooms[] = $expediaRoom;
                            break;
                        }

                        $icePortalRoomKey = array_search($icePortalRoomId, array_column($icePortalRooms, 'supplier_room_id'));
                        if (isset($icePortalRooms[$icePortalRoomKey])) {
                            $romsImagesData[$giataCode][$externalCode][SupplierNameEnum::ICE_PORTAL->value] = $icePortalRooms[$icePortalRoomKey]['images'];
                        }
                        if ($icePortalRoomKey !== false) {
                            $expediaRoom['supplier_codes'] = $mapper;
                            $mergedRooms[] = $expediaRoom;
                            unset($icePortalRooms[$icePortalRoomKey]);
                        } else {
                            $expediaRoom['supplier_codes'] = $mapper;
                            $mergedRooms[] = $expediaRoom;
                        }
                        break;
                    }
                }
                if (! $foundInMapper) {
                    $mergedRooms[] = $expediaRoom;
                }
            }
            $mergedRooms = array_merge($mergedRooms, $icePortalRooms);
            $item['rooms'] = $mergedRooms;
        }
        unset($item);

        return $romsImagesData;
    }

    private function addMissingGiataCodes(array $detailResults, array $missingGiataCodes): array
    {
        foreach ($missingGiataCodes as $giataCode) {
            $detailResults = array_merge($detailResults, [$this->dataTransformer->createEmptyHotelResponse($giataCode)]);
        }

        return $detailResults;
    }

    private function updateDetailResults(array $detailResults, array $structureSource, $repoData, array $resultsIcePortal, array $romsImagesData): array
    {
        foreach ($detailResults as &$result) {
            $giata_code = Arr::get($result, 'giata_hotel_code');
            if (! $giata_code || ! isset($structureSource[$giata_code])) {
                continue;
            }

            $hotel = $repoData->where('giata_code', $giata_code)->first();
            if (! $hotel->product) {
                continue;
            }
            $this->dataTransformer->updateResultWithHotelData($result, $hotel, $structureSource[$giata_code], $resultsIcePortal, $romsImagesData);
        }
        unset($result);

        return $detailResults;
    }

    private function combineContentResults(array $resultsExpedia, array $resultsIcePortal, array $structureSource, $repoData, array $giataCodes): array
    {
        $existingGiataIds = array_column($resultsExpedia, 'giata_hotel_code');
        $filteredResultsIcePortal = array_filter($resultsIcePortal, function ($item) use ($existingGiataIds) {
            return ! in_array($item['giata_hotel_code'], $existingGiataIds);
        });

        $contentResults = array_merge($resultsExpedia, $filteredResultsIcePortal);
        $transformedResultsIcePortal = [];
        foreach ($resultsIcePortal as $item) {
            $giataHotelCode = $item['giata_hotel_code'];
            $transformedResultsIcePortal[$giataHotelCode] = $item;
        }

        foreach ($contentResults as &$result) {
            $giata_code = Arr::get($result, 'giata_hotel_code');
            if (! $giata_code || ! isset($structureSource[$giata_code])) {
                continue;
            }

            $hotel = $repoData->where('giata_code', $giata_code)->first();
            $this->dataTransformer->updateContentResultWithHotelData($result, $hotel, $structureSource[$giata_code], $transformedResultsIcePortal);
        }
        unset($result);

        return $contentResults;
    }

    private function getMissingGiataCodes(array $giataCodes): array
    {
        return array_diff($giataCodes, array_values($this->mappingCacheService->getMappingsExpediaHashMap()));
    }
}
