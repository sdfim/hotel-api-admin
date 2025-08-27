<?php

namespace Modules\API\Controllers\ApiHandlers;

use App\Jobs\SaveBookingItems;
use App\Jobs\SaveSearchInspectorByCacheKey;
use App\Models\Channel;
use App\Models\GeneralConfiguration;
use App\Models\Supplier;
use App\Repositories\ApiSearchInspectorRepository;
use App\Repositories\ChannelRepository;
use App\Traits\Timer;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\API\BaseController;
use Modules\API\Controllers\ApiHandlerInterface;
use Modules\API\Controllers\ApiHandlers\ContentSuppliers\ExpediaHotelController;
use Modules\API\Controllers\ApiHandlers\ContentSuppliers\HiltonHotelController;
use Modules\API\Controllers\ApiHandlers\ContentSuppliers\HotelTraderController;
use Modules\API\Controllers\ApiHandlers\ContentSuppliers\IcePortalHotelController;
use Modules\API\Controllers\ApiHandlers\PricingSuppliers\HbsiHotelController;
use Modules\API\PropertyWeighting\EnrichmentWeight;
use Modules\API\Services\HotelCombinationService;
use Modules\API\Suppliers\Transformers\BaseHotelPricingTransformer;
use Modules\API\Suppliers\Transformers\Expedia\ExpediaHotelContentDetailTransformer;
use Modules\API\Suppliers\Transformers\Expedia\ExpediaHotelContentTransformer;
use Modules\API\Suppliers\Transformers\Expedia\ExpediaHotelPricingTransformer;
use Modules\API\Suppliers\Transformers\HBSI\HbsiHotelPricingTransformer;
use Modules\API\Suppliers\Transformers\Hilton\HiltonHotelContentTransformer;
use Modules\API\Suppliers\Transformers\HotelTrader\HotelTraderContentDetailTransformer;
use Modules\API\Suppliers\Transformers\HotelTrader\HotelTraderHotelPricingTransformer;
use Modules\API\Suppliers\Transformers\IcePortal\IcePortalHotelContentDetailTransformer;
use Modules\API\Suppliers\Transformers\IcePortal\IcePortalHotelContentTransformer;
use Modules\API\Tools\FiberManager;
use Modules\API\Tools\MemoryLogger;
use Modules\API\Tools\PricingDtoTools;
use Modules\API\Tools\PricingRulesTools;
use Modules\Enums\SupplierNameEnum;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Services\SupplierInterface;
use Modules\Inspector\SearchInspectorController;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Throwable;

/**
 * @OA\PathItem(
 * path="/api/content",
 * )
 */
class HotelApiHandler extends BaseController implements ApiHandlerInterface
{
    use Timer;

    // TODO: TEMPORARILY REDUCED TO 0.5 TO AVOID CACHE CLEAR ISSUES IN Modules/API/Tools/ClearSearchCacheByBookingItemsTools.php
    public const TTL = 0.5;

    private const PAGINATION_TO_RESULT = true;

    public function __construct(
        private readonly HbsiHotelPricingTransformer $HbsiHotelPricingTransformer,
        private readonly ExpediaHotelPricingTransformer $expediaHotelPricingTransformer,
        private readonly HotelTraderHotelPricingTransformer $hTraderHotelPricingTransformer,
        private readonly BaseHotelPricingTransformer $baseHotelPricingTransformer,
        private readonly HbsiHotelController $hbsi,
        private readonly HotelTraderController $hTrader,
        private readonly PricingDtoTools $pricingDtoTools,
        private readonly ExpediaHotelController $expedia,
        private readonly IcePortalHotelController $icePortal,
        private readonly HiltonHotelController $hiltonHotel,
        private readonly SearchInspectorController $apiInspector,
        private readonly ExpediaHotelContentTransformer $expediaHotelContentTransformer,
        private readonly IcePortalHotelContentTransformer $icePortalHotelContentTransformer,
        private readonly IcePortalHotelContentDetailTransformer $hbsiHotelContentDetailTransformer,
        private readonly ExpediaHotelContentDetailTransformer $expediaHotelContentDetailTransformer,
        private readonly HiltonHotelContentTransformer $hiltonHotelContentTransformer,
        private readonly HotelTraderContentDetailTransformer $hiltonHotelContentDetailTransformer,
        private readonly EnrichmentWeight $propsWeight,
        private readonly PricingRulesTools $pricingRulesService,
    ) {
        $this->start();
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $filters = $request->all();
            $supplierNames = explode(', ', (GeneralConfiguration::pluck('content_supplier')->toArray()[0] ?? 'Expedia'));
            $keyPricingSearch = $request->type.':contentSearch:'.http_build_query(Arr::dot($filters));
            $tag = 'content_search';
            $keyContent = $keyPricingSearch.':content';
            $keyClientContent = $keyPricingSearch.':clientContent';
            $taggedCache = Cache::tags($tag);

            if ($taggedCache->has($keyContent) && $taggedCache->has($keyClientContent)) {
                $content = $taggedCache->get($keyContent);
                $clientContent = $taggedCache->get($keyClientContent);
            } else {
                $dataResponse = [];
                $clientResponse = [];
                $count = [];
                $totalPages = [];
                $supplierContent = null;
                $supplierContentTransformer = null;

                foreach ($supplierNames as $supplierName) {
                    if (isset($request->supplier) && $request->supplier != $supplierName) {
                        continue;
                    }

                    $this->start($supplierName);

                    if (SupplierNameEnum::from($supplierName) === SupplierNameEnum::EXPEDIA) {
                        $supplierContent = $this->expedia;
                        $supplierContentTransformer = $this->expediaHotelContentTransformer;
                    }
                    if (SupplierNameEnum::from($supplierName) === SupplierNameEnum::ICE_PORTAL) {
                        $supplierContent = $this->icePortal;
                        $supplierContentTransformer = $this->icePortalHotelContentTransformer;
                    }
                    if (SupplierNameEnum::from($supplierName) === SupplierNameEnum::HILTON) {
                        $supplierContent = $this->hiltonHotel;
                        $supplierContentTransformer = $this->hiltonHotelContentTransformer;
                    }

                    if ($supplierContent === null || $supplierContentTransformer === null) {
                        throw new Exception('Supplier content or Transformer is not set');
                    } else {
                        $supplierData = $supplierContent->search($filters);
                        $data = $supplierData['results'];
                        $count[] = $supplierData['count'];
                        $totalPages[] = $supplierData['total_pages'] ?? 0;
                        $dataResponse[$supplierName] = $data;
                        $clientResponse[$supplierName] = $supplierContentTransformer->SupplierToContentSearchResponse($data);
                        Log::debug('HotelApiHandler | search | '.$supplierName.' | runtime '.$this->duration($supplierName));
                    }
                }

                /** Enrichment Property Weighting */
                $clientResponse = $this->propsWeight->enrichmentContent($clientResponse, 'hotel');

                $content = [
                    'count' => $count,
                    'query' => $filters,
                    'results' => $dataResponse,
                ];
                $clientContent = [
                    'count' => $count,
                    'total_pages' => ! empty($totalPages) ? max($totalPages) : 0,
                    'query' => $filters,
                    'results' => $clientResponse,
                ];

                $taggedCache->put($keyContent, $content, now()->addMinutes(self::TTL));
                $taggedCache->put($keyClientContent, $clientContent, now()->addMinutes(self::TTL));
            }

            if ($request->input('supplier_data') == 'true') {
                $res = $content;
            } else {
                $res = $clientContent;
            }

            $res['count'] = Arr::get($res, 'count.0', 0);
            if (count($supplierNames) > 1) {
                $contentSupplier = $request->supplier ?? 'Expedia';
                $res['results']['general'] = $res['results'][$contentSupplier] ?? [];
                $res['content_supplier'] = $contentSupplier;
            } else {
                $res['results']['general'] = $res['results'][$supplierNames[0]] ?? [];
                unset($res['results'][$supplierNames[0]]);
                $res['content_supplier'] = $supplierNames[0];
            }

            return $this->sendResponse($res, 'success');
        } catch (Exception|NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            Log::error('HotelApiHandler | search'.$e->getMessage());
            Log::error($e->getTraceAsString());

            return $this->sendError($e->getMessage(), 'failed');
        }
    }

    public function detail(Request $request): JsonResponse
    {
        $start = microtime(true);

        try {
            $supplierNames = explode(', ', GeneralConfiguration::pluck('content_supplier')->toArray()[0]);
            $keyDetail = $request->type.':contentDetail:'.http_build_query(Arr::dot($request->all()));

            if (Cache::has($keyDetail.':dataResponse') && Cache::has($keyDetail.':clientResponse')) {
                $clientResponse = Cache::get($keyDetail.':clientResponse');
            } else {
                $clientResponse = [];
                $giataCodes = $request->input('property_ids')
                    ? explode(',', $request->input('property_ids'))
                    : ($request->input('property_id') ? [$request->input('property_id')] : []);
                foreach ($supplierNames as $supplierName) {
                    if (isset($request->supplier) && $request->supplier != $supplierName) {
                        continue;
                    }

                    $supplierService = app(SupplierInterface::class, ['supplier' => $supplierName]);
                    $clientResponse[$supplierName] = $supplierService->getResults($giataCodes);
                }

                Cache::put($keyDetail.':clientResponse', $clientResponse, now()->addMinutes(self::TTL));
            }

            $results = $clientResponse;

            $contentSupplier = $request->supplier ?? SupplierNameEnum::EXPEDIA->value;
            $results = $results[$contentSupplier] ?? [];

            $end = microtime(true);
            $executionTime = ($end - $start) * 1000;
            Log::info('HotelApiHandler _ detail _ Execution time: '.$executionTime.' ms');

            return $this->sendResponse(['results' => $results, 'content_supplier' => $contentSupplier], 'success');
        } catch (Exception|NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            Log::error('HotelApiHandler '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return $this->sendError($e->getMessage(), 'failed');
        }
    }

    /**
     * @throws Throwable
     */
    public function price(Request $request, array $suppliers): JsonResponse|StreamedJsonResponse
    {
        MemoryLogger::log('***************************');
        MemoryLogger::log('price_start');
        $stp = $sts = microtime(true);

        try {
            $filters = $request->all();

            $supplierNames = $request->supplier ? explode(',', $request->supplier) : [];
            $supplierIds = Supplier::whereIn('name', $supplierNames)->pluck('id')->toArray();
            if (! empty($supplierIds)) {
                $suppliers = $supplierIds;
            }

            if (self::PAGINATION_TO_RESULT) {
                unset($filters['page']);
                unset($filters['results_per_page']);
            }

            $token = $request->bearerToken();

            $keyPricingSearch = $request->type.':pricingSearch:'.http_build_query(Arr::dot($this->getCacheKeyFromFilters($filters))).':'.$token;
            $tag = 'pricing_search';
            $taggedCache = Cache::tags($tag);

            $executeTime['execution_time_preparation'] = microtime(true) - $sts;
            $sts = microtime(true);

            if ($taggedCache->has($keyPricingSearch.':result')) {
                $res = $taggedCache->get($keyPricingSearch.':result');
            } else {
                if (! isset($filters['rating'])) {
                    $filters['rating'] = GeneralConfiguration::latest()->first()->star_ratings ?? 3;
                }

                $search_id = (string) Str::uuid();
                $filters['token_id'] = $token;
                $searchInspector = ApiSearchInspectorRepository::newSearchInspector([$search_id, $filters, $suppliers, 'price', 'hotel']);

                /** @var FiberManager $fiberManager */
                $fiberManager = app(FiberManager::class);

                $supplierResponses = $pricingRules = $pricingExclusionRules = [];
                $suppliersGiataIds = $dataResponse = $clientResponse = $bookingItems = $dataOriginal = $totalPages = [];
                $countResponse = $countClientResponse = 0;
                $filters['query_package'] ??= 'package';

                foreach ($suppliers as $supplierId) {
                    $supplier = Supplier::find($supplierId)?->name;
                    if ($request->supplier) {
                        $supplierQuery = explode(',', $request->supplier);
                        if (! in_array($supplier, $supplierQuery)) {
                            continue;
                        }
                    }

                    if ($supplier === SupplierNameEnum::EXPEDIA->value) {
                        $optionsQueries = $filters['query_package'] === 'both' ? ['standalone', 'package'] : [$filters['query_package']];
                    } else {
                        $optionsQueries = ['any'];
                    }

                    $preSearchData = $this->getPreSearchData($supplier, $filters);

                    /** @var array<int, string> $rawGiataIds key - giata_id, value - supplier_id */
                    $rawGiataIds = match (SupplierNameEnum::from($supplier)) {
                        SupplierNameEnum::HBSI => Arr::get($preSearchData, 'rawGiataIds', []),
                        SupplierNameEnum::EXPEDIA => $preSearchData,
                        SupplierNameEnum::HOTEL_TRADER => $preSearchData,
                        default => [],
                    };

                    MemoryLogger::log('preSearchData_'.$supplier);

                    $this->applyBlueprintFiltering($rawGiataIds, $filters);
                    $this->applyDriverFiltering($rawGiataIds, $supplier);

                    $suppliersGiataIds[SupplierNameEnum::from($supplier)->value] = array_merge(
                        $suppliersGiataIds[SupplierNameEnum::from($supplier)->value] ?? [],
                        $rawGiataIds
                    );

                    foreach ($optionsQueries as $optionsQuery) {
                        $fiberKey = $supplier.'_'.$optionsQuery;
                        $currentFilters = [...$filters, 'query_package' => $optionsQuery];

                        $fiberManager->add($fiberKey, function () use (
                            $supplier,
                            $currentFilters,
                            $searchInspector,
                            $rawGiataIds
                        ) {
                            $result = match (SupplierNameEnum::from($supplier)) {
                                SupplierNameEnum::EXPEDIA => $this->expedia->price($currentFilters, $searchInspector, $rawGiataIds),
                                SupplierNameEnum::HBSI => $this->hbsi->price($currentFilters, $searchInspector, $rawGiataIds),
                                SupplierNameEnum::HOTEL_TRADER => $this->hTrader->price($currentFilters, $searchInspector, $rawGiataIds),
                                default => throw new Exception("Unknown supplier: $supplier")
                            };

                            return $result;
                        });
                    }
                }

                $supplierRequestGiataIds = array_merge(...array_values($suppliersGiataIds));

                $fiberManager->add('transformer', function () use ($search_id, $supplierRequestGiataIds) {
                    $this->baseHotelPricingTransformer->fetchSupplierRepositoryData($search_id, $supplierRequestGiataIds);
                }, false);

                $fiberManager->add('pricingRules', function () use ($filters, $supplierRequestGiataIds) {
                    return app(PricingRulesTools::class)->rules($filters, $supplierRequestGiataIds);
                }, false);

                $fiberManager->add('pricingExclusionRules', function () use ($filters, $supplierRequestGiataIds) {
                    return app(PricingRulesTools::class)->rules($filters, $supplierRequestGiataIds, true);
                }, false);

                MemoryLogger::log('fiberManager_add');

                $startTime = microtime(true);

                $fiberManager->startAll();
                $fiberManager->wait();
                $resume = $fiberManager->getResume();

                foreach ($fiberManager->getFibers() as $fiber_key => $fiber) {
                    match ($fiber_key) {
                        'transformer' => null,
                        'pricingRules' => $pricingRules = $fiber->getReturn(),
                        'pricingExclusionRules' => $pricingExclusionRules = $fiber->getReturn(),
                        default => [
                            [$supplierName, $queryPackage] = explode('_', $fiber_key),
                            $fiber->isSuspended() && $fiber->resume($resume[$supplierName][$queryPackage] ?? null),
                            $fiber->isTerminated() && $supplierResponses[$fiber_key] = $fiber->getReturn(),
                        ],
                    };
                }

                $executeTime['execution_time_fiber'] = microtime(true) - $startTime;

                Log::info('HotelApiHandler _ price _ count_pricing_rules ', [
                    'count_pricingRules' => count($pricingRules),
                    'pricingRules' => $pricingRules,
                    'supplierRequestGiataIds' => $supplierRequestGiataIds,
                ]);

                MemoryLogger::log('fiberManager_startAll');

                foreach ($supplierResponses as $fiber_key => $supplierResponse) {
                    [$supplierName, $queryPackage] = explode('_', $fiber_key);
                    $currentFilters = [...$filters, 'query_package' => $queryPackage];

                    $result = $this->handlePriceSupplier(
                        $supplierResponse,
                        $supplierName,
                        $currentFilters,
                        $search_id,
                        $pricingRules,
                        $pricingExclusionRules,
                        $suppliersGiataIds[$supplierName]
                    );

                    if ($error = Arr::get($result, 'error')) {
                        return $this->sendError($error, 'failed');
                    }

                    if (! str_contains($fiber_key, SupplierNameEnum::EXPEDIA->value)) {
                        $fiber_key = $supplierName;
                    }

                    $dataResponse[$fiber_key] = $result['dataResponse'][$supplierName];
                    $clientResponse[$fiber_key] = $result['clientResponse'][$supplierName];
                    $totalPages[$fiber_key] = $result['totalPages'][$supplierName];
                    $bookingItems[$fiber_key] = $result['bookingItems'][$supplierName] ?? [];
                    $dataOriginal[$fiber_key] = $result['dataOriginal'][$supplierName] ?? [];
                    $countResponse += $result['countResponse'];
                    $countClientResponse += $result['countClientResponse'];

                    unset($supplierResponse);
                    gc_collect_cycles();
                }

                MemoryLogger::log('handlePriceSupplier');

                /** Expedia RS aggregation If the 'query_package' key is set to 'both' */
                if ($filters['query_package'] === 'both' && isset($clientResponse['Expedia_standalone'], $clientResponse['Expedia_package'])) {
                    $clientResponse['Expedia_both'] = $this->pricingDtoTools->mergeHotelData($clientResponse['Expedia_standalone'], $clientResponse['Expedia_package']);
                    unset($clientResponse['Expedia_standalone']);
                    unset($clientResponse['Expedia_package']);
                }

                $executeTime['execution_time_transformer'] = microtime(true) - $sts - $executeTime['execution_time_fiber'];
                $sts = microtime(true);

                /** Enrichment Property Weighting */
                $enrichClientResponse = $this->propsWeight->enrichmentPricing($clientResponse, 'hotel');
                $executeTime['execution_time_weighting'] = microtime(true) - $sts;

                if (! isset($filters['view_ids'])) {
                    unset($filters['ids']);
                }
                $content = ['count' => $countResponse, 'query' => $filters, 'results' => $dataResponse];

                $clientContentWithPricingRules = [
                    'count' => $countClientResponse,
                    'total_pages' => count($totalPages) === 0 ? 0 : max($totalPages),
                    'query' => $filters,
                    'results' => $enrichClientResponse,
                ];

                $clientContent = [
                    'count' => $countClientResponse,
                    'total_pages' => ! empty($totalPages) ? max($totalPages) : 0,
                    'query' => $filters,
                    'results' => $this->removePricingRulesApplier($enrichClientResponse),
                ];

                MemoryLogger::log('price_end');

                /** Save data to Inspector */
                $cacheKeys = [];
                foreach (['dataOriginal', 'content', 'clientContent', 'clientContentWithPricingRules'] as $variableName) {
                    $key = $variableName.'_'.uniqid();
                    $cacheKeys[$variableName] = $key;
                    Cache::put($key, gzcompress(json_encode($$variableName)), now()->addMinutes(10));
                }
                // this approach is more memory-efficient.
                SaveSearchInspectorByCacheKey::dispatch($searchInspector, $cacheKeys);

                MemoryLogger::log('SaveSearchInspectorByCacheKey');

                if (! empty($bookingItems)) {
                    foreach ($bookingItems as $items) {
                        SaveBookingItems::dispatch($items);
                    }
                }

                if ($request->input('supplier_data') == 'true') {
                    $res = $content;
                } else {
                    $res = $clientContent;
                }

                $res['search_id'] = $search_id;

                $taggedCache->put($keyPricingSearch.':result', $res, now()->addMinutes(self::TTL));
            }

            $res = $this->applyFilters($res);
            $executeTime['execution_time_all'] = microtime(true) - $stp;
            Log::info('HotelApiHandler _ price _ executeTime '.$executeTime['execution_time_all'].' seconds', [
                ...$executeTime,
                'supplier' => $request->supplier ?? 'all',
                'count' => $res['count'],
                'timestamp' => now()->toDateTimeString(),
            ]);

            if (self::PAGINATION_TO_RESULT) {
                //                $res = $this->paginate($res, $request->input('page', 1), $request->input('results_per_page', 10));
                $res = $this->combinedAndPaginate($res, $request->input('page', 1), $request->input('results_per_page', 50), $res['query']);
            }

            return $this->sendResponse($res, 'success', 200, true);
        } catch (Exception|NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            Log::error('HotelApiHandler '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return $this->sendError($e->getMessage(), 'failed');
        } finally {
            MemoryLogger::log('price_end');
        }
    }

    private function removePricingRulesApplier(array $response): array
    {
        return array_map(function ($supplierData) {
            return array_map(function ($hotel) {
                if (isset($hotel['room_groups'])) {
                    $hotel['room_groups'] = array_map(function ($roomGroup) {
                        if (isset($roomGroup['rooms'])) {
                            $roomGroup['rooms'] = array_map(function ($room) {
                                unset($room['pricing_rules_applier']); // Remove the key

                                return $room;
                            }, $roomGroup['rooms']);
                        }

                        return $roomGroup;
                    }, $hotel['room_groups']);
                }

                return $hotel;
            }, $supplierData);
        }, $response);
    }

    private function getPreSearchData(string $supplier, array $filters): mixed
    {
        return match (SupplierNameEnum::from($supplier)) {
            SupplierNameEnum::HBSI => $this->hbsi->preSearchData($filters),
            SupplierNameEnum::HOTEL_TRADER => $this->hTrader->preSearchData($filters, 'price'),
            SupplierNameEnum::EXPEDIA => $this->expedia->preSearchData($filters, 'price'),
            default => throw new Exception("Unknown supplier: $supplier"),
        };
    }

    private function filterByPrice(array $target, $maxPriceFilter, $minPriceFilter): array
    {
        return collect($target)->filter(function ($hotel) use ($maxPriceFilter, $minPriceFilter) {
            $hotelMinPrice = $hotel['lowest_priced_room_group'];

            return ($maxPriceFilter >= $hotelMinPrice || $maxPriceFilter === null) &&
                ($minPriceFilter <= $hotelMinPrice || $minPriceFilter === null);
        })->toArray();
    }

    private function applyFilters(array $result): array
    {
        $filters = $result['query'];
        $maxPriceFilter = Arr::get($filters, 'max_price', null);
        $minPriceFilter = Arr::get($filters, 'min_price', null);

        $output = Arr::get($result, 'results.Expedia_both', []);
        $value = $this->filterByPrice($output ?? [], $maxPriceFilter, $minPriceFilter);
        $result['results']['Expedia_both'] = $value;

        $output = Arr::get($result, 'results.HBSI', []);
        $value = $this->filterByPrice($output ?? [], $maxPriceFilter, $minPriceFilter);
        $result['results']['HBSI'] = $value;

        return $result;
    }

    /**
     * Paginate the given results.
     *
     * @param  array  $results  The results to paginate.
     * @param  int  $page  The current page number.
     * @param  int  $resultsPerPage  The number of results per page.
     * @return array The paginated results.
     */
    public function paginate(array $results, int $page, int $resultsPerPage): array
    {
        $supplierResults = $results['results'];
        $totalPages = [];

        // Calculate the offset
        $offset = ($page - 1) * $resultsPerPage;

        foreach ($supplierResults as $key => $result) {
            // Calculate the total number of pages
            $totalPages[$key] = ceil(count($result) / $resultsPerPage);

            // Slice the results array to get only the results for the current page
            $supplierResults[$key] = array_slice($result, $offset, $resultsPerPage);

            $factPerPage[$key] = count($supplierResults[$key]);
        }

        $results['total_pages'] = max($totalPages);
        $results['results'] = $supplierResults;
        $results['query']['page'] = $page;
        $results['query']['results_per_page'] = $resultsPerPage;
        $results['count_per_page'] = max($factPerPage);

        return $results;
    }

    public function combinedAndPaginate(array $results, int $page, int $resultsPerPage, array $filters): array
    {
        // Merge all supplier results into one array
        $mergedResults = [];
        foreach ($results['results'] as $supplierResults) {
            $mergedResults = array_merge($mergedResults, $supplierResults);
        }

        if (Arr::get($filters, 'order') === 'cheapest_price') {
            $mergedResults = collect($mergedResults)->sortBy('lowest_priced_room_group')->values()->toArray();
        } else {
            usort($mergedResults, function ($a, $b) use ($results) {

                if (Arr::has($results, 'query.latitude')) {
                    return ($a['distance'] < $b['distance']) ? -1 : 1;
                }

                // Check if 'weight' key exists and is not zero for both items
                $aWeightExists = isset($a['weight']) && $a['weight'] != 0;
                $bWeightExists = isset($b['weight']) && $b['weight'] != 0;

                // If 'weight' key exists and is not zero for both items, compare these
                if ($aWeightExists && $bWeightExists) {
                    return $b['weight'] <=> $a['weight']; // Changed order for descending sort
                }

                // If 'weight' key exists and is not zero only for one item, that item should come first
                if ($aWeightExists) {
                    return -1; // $a comes first
                }
                if ($bWeightExists) {
                    return 1; // $b comes first
                }

                // If 'weight' key does not exist or is zero for both items, compare 'lowest_priced_room_group'
                return $a['lowest_priced_room_group'] <=> $b['lowest_priced_room_group'];
            });
        }

        // Calculate the offset
        $offset = ($page - 1) * $resultsPerPage;

        // Slice the merged results array to get only the results for the current page
        $pagedResults = array_slice($mergedResults, $offset, $resultsPerPage);

        // Calculate the total number of pages
        $totalPages = ceil(count($mergedResults) / $resultsPerPage);

        $results['total_pages'] = $totalPages;
        $results['results'] = $pagedResults;
        $results['query']['page'] = $page;
        $results['query']['results_per_page'] = $resultsPerPage;
        $results['count_per_page'] = count($pagedResults);
        $results['count'] = count($mergedResults);

        return $results;
    }

    /**
     * @throws Throwable
     */
    private function handlePriceSupplier($supplierResponse, string $supplierName, array $filters, string $search_id, array $pricingRules, array $pricingExclusionRules, array $giataIds): array
    {
        $dataResponse = [];
        $clientResponse = [];
        $totalPages = [];
        $bookingItems = [];
        $countResponse = 0;
        $countClientResponse = 0;
        $dataOriginal = [];

        if (SupplierNameEnum::from($supplierName) === SupplierNameEnum::HOTEL_TRADER) {
            $hTraderResponse = $supplierResponse;

            $dataResponse[$supplierName] = $hTraderResponse['array'];
            $dataOriginal[$supplierName] = $hTraderResponse['original'];

            $st = microtime(true);
            $hotelGenerator = $this->hTraderHotelPricingTransformer->HotelTraderToHotelResponse($hTraderResponse['array'], $filters, $search_id, $pricingRules, $pricingExclusionRules, $giataIds);
            $clientResponse[$supplierName] = [];
            $count = 0;
            $hotels = [];
            foreach ($hotelGenerator as $count => $hotel) {
                $hotels[] = $hotel;
            }

            /** Enrichment Room Combinations */
            $countRooms = count($filters['occupancy']);
            if ($countRooms > 1) {
                $hotelService = new HotelCombinationService(SupplierNameEnum::HOTEL_TRADER->value);
                $clientResponse[$supplierName] = $hotelService->enrichmentRoomCombinations($hotels, $filters);
            } else {
                $clientResponse[$supplierName] = $hotels;
            }
            $bookingItems[$supplierName] = $this->hTraderHotelPricingTransformer->bookingItems ?? ($hotelGenerator['bookingItems'] ?? []);

            $countResponse += count($hTraderResponse['array']);
            $totalPages[$supplierName] = $hTraderResponse['total_pages'] ?? 0;
            $countClientResponse += $count;
            Log::info('HotelApiHandler _ price _ Transformer HotelTraderToHotelResponse '.(microtime(true) - $st).' seconds');
            unset($hTraderResponse, $hotelGenerator);
        }

        if (SupplierNameEnum::from($supplierName) === SupplierNameEnum::EXPEDIA) {
            $expediaResponse = $supplierResponse;

            $dataResponse[$supplierName] = $expediaResponse['array'];
            $dataOriginal[$supplierName] = $expediaResponse['original'];

            $countResponse += count($expediaResponse['array']);
            $totalPages[$supplierName] = $expediaResponse['total_pages'] ?? 0;

            $st = microtime(true);
            $hotelGenerator = $this->expediaHotelPricingTransformer->ExpediaToHotelResponse($expediaResponse['array'], $filters, $search_id, $pricingRules, $pricingExclusionRules, $giataIds);

            $clientResponse[$supplierName] = [];
            $count = 0;
            foreach ($hotelGenerator as $count => $hotel) {
                $clientResponse[$supplierName][] = $hotel;
            }
            $bookingItems[$supplierName] = $this->expediaHotelPricingTransformer->bookingItems;
            $countClientResponse += $count;
            Log::info('HotelApiHandler _ price _ Transformer ExpediaToHotelResponse '.(microtime(true) - $st).' seconds');
            unset($expediaResponse, $hotelGenerator);
        }

        if (SupplierNameEnum::from($supplierName) === SupplierNameEnum::HBSI) {
            $hbsiResponse = $supplierResponse;

            $dataResponse[$supplierName] = $hbsiResponse['array'];
            $dataOriginal[$supplierName] = $hbsiResponse['original'];

            $st = microtime(true);
            $hotelGenerator = $this->HbsiHotelPricingTransformer->HbsiToHotelResponse($hbsiResponse['array'], $filters, $search_id, $pricingRules, $pricingExclusionRules, $giataIds);
            $clientResponse[$supplierName] = [];
            $count = 0;
            $hotels = [];
            foreach ($hotelGenerator as $count => $hotel) {
                $hotels[] = $hotel;
            }
            /** Enrichment Room Combinations */
            $countRooms = count($filters['occupancy']);
            if ($countRooms > 1) {
                $hotelService = new HotelCombinationService(SupplierNameEnum::HBSI->value);
                $clientResponse[$supplierName] = $hotelService->enrichmentRoomCombinations($hotels, $filters);
            } else {
                $clientResponse[$supplierName] = $hotels;
            }
            $bookingItems[$supplierName] = $this->HbsiHotelPricingTransformer->bookingItems ?? ($hotelGenerator['bookingItems'] ?? []);

            $countResponse += count($hbsiResponse['array']);
            $totalPages[$supplierName] = $hbsiResponse['total_pages'] ?? 0;
            $countClientResponse += count($clientResponse[$supplierName]);
            Log::info('HotelApiHandler _ price _ Transformer HbsiToHotelResponse '.(microtime(true) - $st).' seconds');
            unset($hbsiResponse, $hotelGenerator, $hotels);
        }

        return [
            'error' => Arr::get($supplierResponse, 'error'),
            'dataResponse' => $dataResponse,
            'clientResponse' => $clientResponse,
            'countResponse' => $countResponse,
            'totalPages' => $totalPages,
            'countClientResponse' => $countClientResponse,
            'bookingItems' => $bookingItems ?? [],
            'dataOriginal' => $dataOriginal ?? [],
        ];
    }

    private function getCacheKeyFromFilters(array $filters): array
    {
        $_filters = [...$filters];

        unset($_filters['session']);

        return $_filters;
    }

    /******** Utility function to resolve force parameters based on channel settings ******/

    private function resolveForceParams(): array
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
        ];
    }

    private function applyBlueprintFiltering(array &$rawGiataIds, array &$filters): void
    {
        $forceParams = $this->resolveForceParams();
        $blueprintExists = Arr::get($forceParams, 'blueprint_exists', true);
        $forceVerified = Arr::get($forceParams, 'force_verified', false);
        $forceOnSale = Arr::get($forceParams, 'force_on_sale', false);

        $exclude = [];
        if ($blueprintExists) {
            $filteredGiataIds = Hotel::whereIn('giata_code', array_values($rawGiataIds))
                ->whereHas('product')
                ->pluck('giata_code')
                ->toArray();

            $rawGiataIds = array_intersect($rawGiataIds, $filteredGiataIds);

            $this->extracted($forceVerified, $forceOnSale, $exclude, $rawGiataIds);
        } else {
            $this->extracted($forceVerified, $forceOnSale, $exclude, $rawGiataIds);
        }

        $rawGiataIds = array_diff($rawGiataIds, array_unique($exclude));

        $filters['force_on_sale'] = $forceOnSale;
        $filters['force_verified'] = $forceVerified;
        $filters['filtered_giata_ids'] = array_values($rawGiataIds);
    }

    private function applyDriverFiltering(array &$giataIds, string $supplier): void
    {
        if (empty($giataIds)) {
            return;
        }

        $driverName = $supplier;

        $hotelsWithDisabledDriver = Hotel::whereIn('giata_code', $giataIds)
            ->whereHas('product', function ($q) use ($driverName) {
                $q->where(function ($subQ) use ($driverName) {
                    $subQ->where('off_sale_by_sources', '[]')
                        ->orWhereRaw("JSON_SEARCH(off_sale_by_sources, 'one', ?) IS NULL", [$driverName]);
                });
            })
            ->pluck('giata_code')
            ->toArray();

        $giataIds = array_diff($giataIds, $hotelsWithDisabledDriver);
    }

    private function extracted(mixed $forceVerified, mixed $forceOnSale, array &$exclude, array $rawGiataIds): void
    {
        if (! $forceVerified) {
            $exclude = array_merge($exclude, Hotel::whereIn('giata_code', array_values($rawGiataIds))
                ->whereHas('product', function ($q) {
                    $q->where('verified', 0);
                })
                ->pluck('giata_code')
                ->toArray());
        }

        if (! $forceOnSale) {
            $exclude = array_merge($exclude, Hotel::whereIn('giata_code', array_values($rawGiataIds))
                ->whereHas('product', function ($q) {
                    $q->where('onSale', 0);
                })
                ->pluck('giata_code')
                ->toArray());
        }
    }
}
