<?php

namespace Modules\HotelContentRepository\Services;

use App\Models\Channel;
use App\Models\GeneralConfiguration;
use App\Repositories\ChannelRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Modules\API\Controllers\ApiHandlers\ContentSuppliers\SupplierControllerInterface;
use Modules\API\Suppliers\Transformers\SupplierContentTransformerInterface;
use Modules\Enums\SupplierNameEnum;
use Modules\HotelContentRepository\Models\Hotel;

class HotelContentApiService
{
    public function __construct(
        private readonly HotelContentApiTransformerService $dataTransformer,
    ) {}

    public function sortAndPaginate(array $contentResults, int $page, int $resultsPerPage): array
    {
        usort($contentResults, function ($a, $b) {
            return $b['weight'] <=> $a['weight'];
        });

        $offset = ($page - 1) * $resultsPerPage;

        return array_slice($contentResults, $offset, $resultsPerPage);
    }

    public function generateCacheKey(Request $request): string
    {
        $queryParams = $request->except(['page', 'results_per_page']);

        return $request->type.':contentDetail:'.http_build_query(Arr::dot($queryParams));
    }

    public function fetchContentResults(Request $request): array
    {
        $results = $transformedResults = [];

        foreach (SupplierNameEnum::getContentSupplierValues() as $supplier) {
            /** @var SupplierControllerInterface $service */
            $service = app(SupplierControllerInterface::class, ['supplier' => $supplier]);
            /** @var SupplierContentTransformerInterface $transformer */
            $transformer = app(SupplierContentTransformerInterface::class, ['supplier' => $supplier]);
            $supplierResults = Arr::get($service->search($request->all()), 'results', []);
            $results = array_merge($results, $supplierResults);
            $transformedResults[$supplier] = $transformer->SupplierToContentSearchResponse($supplierResults);
        }

        $giataCodes = $this->getGiataCodesByContent($results);
        ['channel' => $channel, 'force_verified' => $forceVerified, 'force_on_sale' => $forceOnSale, 'blueprint_exists' => $blueprintExists] = $this->resolveChannelAndForceParams();
        $this->applyVisibilityFiltersToGiataCodes($giataCodes, $channel, $forceVerified, $forceOnSale, $blueprintExists);
        $contentSource = $this->dataTransformer->initializeContentSource($giataCodes);
        $repoData = $this->getRepoData($giataCodes);
        $structureSource = $this->dataTransformer->buildStructureSource($repoData, $contentSource);

        return $this->combineContentResults($transformedResults, $structureSource, $repoData, $giataCodes);
    }

    public function fetchDetailResults(array $giataCodes): array
    {
        ['channel' => $channel, 'force_verified' => $forceVerified, 'force_on_sale' => $forceOnSale, 'blueprint_exists' => $blueprintExists] = $this->resolveChannelAndForceParams();
        $this->applyVisibilityFiltersToGiataCodes($giataCodes, $channel, $forceVerified, $forceOnSale, $blueprintExists);
        $contentSource = $this->dataTransformer->initializeContentSource($giataCodes);
        $repoData = $this->getRepoData($giataCodes);
        $structureSource = $this->dataTransformer->buildStructureSource($repoData, $contentSource);

        $resultsSuppliers = [];
        foreach (SupplierNameEnum::getContentSupplierValues() as $supplier) {
            /** @var SupplierInterface $service */
            $service = app(SupplierInterface::class, ['supplier' => $supplier]);
            $results = $service->getResults($giataCodes);
            $resultsSuppliers[$supplier] = $results;
        }

        return $this->combineDetailResults($resultsSuppliers, $structureSource, $repoData, $giataCodes);
    }

    private function getSupplierNames(): array
    {
        return explode(', ', GeneralConfiguration::pluck('content_supplier')->toArray()[0]);
    }

    public function getGiataCodes(Request $request): array
    {
        $ids = $request->input('property_ids') ?? $request->input('giata_ids');

        return $ids ? explode(',', str_replace(' ', '', $ids)) : [$request->input('property_id')];
    }

    private function getGiataCodesByContent(array ...$supplierResults): array
    {
        $giataCodes = [];
        foreach ($supplierResults as $results) {
            $giataCodes = array_merge($giataCodes, array_column($results, 'giata_id'));
        }

        return $giataCodes;
    }

    private function getHotelsByOnSaleStatus(array $giataCodes, int $onSaleStatus): Collection
    {
        return Hotel::whereIn('giata_code', $giataCodes)
            ->whereHas('product', function ($query) use ($onSaleStatus) {
                $query->where('onSale', $onSaleStatus);
            })
            ->get();
    }

    private function applyVisibilityFiltersToGiataCodes(array &$giataCodes, Channel $channel, ?bool $forceVerified, ?bool $forceOnSale, ?bool $blueprintExists): void
    {
        $filteredGiataIds = $giataCodes;

        // Whitelist check
        $hotelsNotWhiteList = [];
        if ($channel) {
            $hotelsNotWhiteList = Hotel::with('product.channels')
                ->whereIn('giata_code', $giataCodes)
                ->whereDoesntHave('product.channels', function ($query) use ($channel) {
                    $query->where('channel_id', $channel->id);
                })
                ->get()->pluck('giata_code')->toArray();

            $hotelsWithoutWhiteList = Hotel::with('product.channels')
                ->whereDoesntHave('product.channels')
                ->get()->pluck('giata_code')->toArray();

            $hotelsNotWhiteList = array_diff($hotelsNotWhiteList, $hotelsWithoutWhiteList);
            $filteredGiataIds = array_diff($filteredGiataIds, $hotelsNotWhiteList);
        }

        if ($blueprintExists) {
            $query = Hotel::whereIn('giata_code', $filteredGiataIds)
                ->whereHas('product');
            $filteredGiataIds = $query->pluck('giata_code')->toArray();

            if (!$forceVerified) {
                $verifiedQuery = Hotel::whereIn('giata_code', $filteredGiataIds)
                    ->whereHas('product', function ($q) {
                        $q->where('verified', 0);
                    });
                $filteredGiataIds = array_diff($filteredGiataIds, $verifiedQuery->pluck('giata_code')->toArray());
            }

            if (!$forceOnSale) {
                $onSaleQuery = Hotel::whereIn('giata_code', $filteredGiataIds)
                    ->whereHas('product', function ($q) {
                        $q->where('onSale', 0);
                    });
                $filteredGiataIds = array_diff($filteredGiataIds, $onSaleQuery->pluck('giata_code')->toArray());
            }
        } else {
            if (!$forceVerified) {
                $verifiedQuery = Hotel::whereIn('giata_code', $filteredGiataIds)
                    ->whereHas('product', function ($q) {
                        $q->where('verified', 0);
                    });
                $filteredGiataIds = array_diff($filteredGiataIds, $verifiedQuery->pluck('giata_code')->toArray());
            }

            if (!$forceOnSale) {
                $onSaleQuery = Hotel::whereIn('giata_code', $filteredGiataIds)
                    ->whereHas('product', function ($q) {
                        $q->where('onSale', 0);
                    });
                $filteredGiataIds = array_diff($filteredGiataIds, $onSaleQuery->pluck('giata_code')->toArray());
            }
        }

        $giataCodes = $filteredGiataIds;
    }

    public function getRepoData(array $giataCodes): ?Collection
    {
        ['channel' => $channel, 'force_verified' => $forceVerified, 'force_on_sale' => $forceOnSale] = $this->resolveChannelAndForceParams();
        
        $query = Hotel::whereIn('giata_code', $giataCodes);

        if (!$forceOnSale && !$forceVerified) {
            $query->whereHas('product', function ($productQuery) use ($forceVerified, $forceOnSale) {
                if (!$forceOnSale) {
                    $productQuery->where('onSale', 1);
                }
                if (!$forceVerified) {
                    $productQuery->where('verified', 1);
                }
            });
        }

        $hotels = $query->get();

        foreach ($hotels as $hotel) {
            if (! $hotel->product) {
                unset($hotels[$hotel->giata_code]);
            }
        }

        return $hotels;
    }

    private function combineDetailResults(array $resultsSuppliers, array $structureSource, $repoData, array $giataCodes): array
    {
        $rooms = $this->getRooms($repoData, $giataCodes);
        $roomMappers = $this->getRoomMappers($rooms);

        $existingExpediaGiataIds = array_column($resultsSuppliers[SupplierNameEnum::EXPEDIA->value], 'giata_hotel_code');
        $detailResults = $resultsSuppliers[SupplierNameEnum::EXPEDIA->value];

        foreach (SupplierNameEnum::getContentSupplierValues() as $supplier) {
            if ($supplier !== SupplierNameEnum::EXPEDIA->value) {
                $filteredResults = $this->rejectByExistingGiataCode($resultsSuppliers[$supplier], $existingExpediaGiataIds);
                $detailResults = array_merge($detailResults, $filteredResults);
            }
        }

        if (empty($detailResults)) {
            return [];
        }

        $romsImagesData = $this->mergeRooms($detailResults, $resultsSuppliers, $roomMappers);

        return $this->updateDetailResults($detailResults, $structureSource, $repoData, $resultsSuppliers, $romsImagesData);
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
                    $mapper['external_code'] = $room['external_code'];
                }
                $roomMappers[$giataCode][] = $mapper;
            }
        }

        return $roomMappers;
    }

    private function rejectByExistingGiataCode(array $resultsDetail, array $existingExpediaGiataIds): array
    {
        return array_filter($resultsDetail, function ($item) use ($existingExpediaGiataIds) {
            return ! in_array($item['giata_hotel_code'], $existingExpediaGiataIds);
        });
    }

    private function mergeRooms(array &$detailResults, array $resultsSuppliers, array $roomMappers): array
    {
        $romsImagesData = [];
        foreach ($detailResults as &$item) {
            $giataCode = $item['giata_hotel_code'];
            $mergedRooms = [];

            foreach ($resultsSuppliers as $supplier => $results) {
                $supplierKey = array_search($giataCode, array_column($results, 'giata_hotel_code'));
                $supplierRooms = $supplierKey !== false ? $results[$supplierKey]['rooms'] : [];

                foreach ($supplierRooms as $supplierRoom) {
                    $unifiedRoomCode = $supplierRoom['unified_room_code'];
                    $foundInMapper = false;

                    if (! isset($roomMappers[$giataCode])) {
                        $mergedRooms[] = $supplierRoom;

                        continue;
                    }

                    foreach ($roomMappers[$giataCode] as $mapper) {
                        $externalCode = $mapper['external_code'];

                        if (in_array($unifiedRoomCode, $mapper)) {
                            $romsImagesData[$giataCode][$externalCode][$supplier] = $supplierRoom['images'];
                            $foundInMapper = true;

                            foreach ($resultsSuppliers as $innerSupplier => $innerResults) {
                                if ($innerSupplier === $supplier) {
                                    continue;
                                }

                                $innerRoomId = Arr::get($mapper, $innerSupplier);
                                if ($innerRoomId) {
                                    $innerRoomKey = array_search($innerRoomId, array_column($innerResults, 'unified_room_code'));
                                    if (isset($innerResults[$innerRoomKey])) {
                                        $romsImagesData[$giataCode][$externalCode][$innerSupplier] = $innerResults[$innerRoomKey]['images'];
                                    }
                                    $supplierRoom['supplier_codes'] = $mapper;
                                    $mergedRooms[] = $supplierRoom;
                                    if ($innerRoomKey !== false) {
                                        unset($innerResults[$innerRoomKey]);
                                    }
                                    break;
                                }
                            }
                        }
                    }
                    if (! $foundInMapper) {
                        $mergedRooms[] = $supplierRoom;
                    }
                }
            }
            $item['rooms'] = $mergedRooms;
        }
        unset($item);

        return $romsImagesData;
    }

    private function updateDetailResults(array $detailResults, array $structureSource, $repoData, array $resultsSuppliers, array $romsImagesData): array
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
            $this->dataTransformer->updateResultWithHotelData($result, $hotel, $structureSource[$giata_code], $resultsSuppliers, $romsImagesData);
        }
        unset($result);

        return $detailResults;
    }

    private function combineContentResults(array $resultsSuppliers, array $structureSource, $repoData, array $giataCodes): array
    {
        $existingGiataIds = array_column($resultsSuppliers[SupplierNameEnum::EXPEDIA->value], 'giata_hotel_code');
        $contentResults = $resultsSuppliers[SupplierNameEnum::EXPEDIA->value];

        foreach (SupplierNameEnum::getContentSupplierValues() as $supplier) {
            if ($supplier !== SupplierNameEnum::EXPEDIA->value) {
                $filteredResults = $this->rejectByExistingGiataCode($resultsSuppliers[$supplier], $existingGiataIds);
                $contentResults = array_merge($contentResults, $filteredResults);
            }
        }

        $contentResults = array_filter($contentResults, function ($item) use ($giataCodes) {
            return in_array($item['giata_hotel_code'], $giataCodes);
        });

        $transformedResults = [];
        foreach ($resultsSuppliers as $supplier => $items) {
            foreach ($items as $item) {
                $giataHotelCode = $item['giata_hotel_code'];
                $transformedResults[$supplier][$giataHotelCode] = $item;
            }
        }

        foreach ($contentResults as &$result) {
            $giata_code = Arr::get($result, 'giata_hotel_code');
            if (! $giata_code || ! isset($structureSource[$giata_code])) {
                continue;
            }

            $hotel = $repoData->where('giata_code', $giata_code)->first();
            $this->dataTransformer->updateContentResultWithHotelData($result, $hotel, $structureSource[$giata_code], $transformedResults);
        }
        unset($result);

        return $contentResults;
    }

    private function resolveChannelAndForceParams(): array
    {
        $token_id = ChannelRepository::getTokenId(request()->bearerToken());
        $channel = Channel::where('token_id', $token_id)->first();

        $forceVerified = false;
        $forceOnSale = false;
        $blueprintExists = request('blueprint_exists', true);

        if ($channel && $channel->accept_special_params) {
            $forceVerified = request('force_verified_on', false);
            $forceOnSale = request('force_on_sale_on', false);
        }

        return [
            'force_verified' => $forceVerified,
            'force_on_sale' => $forceOnSale,
            'blueprint_exists' => $blueprintExists,
            'channel' => $channel,
        ];
    }

}
