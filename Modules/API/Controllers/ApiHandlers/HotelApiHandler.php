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
use Modules\API\PropertyWeighting\EnrichmentWeight;
use Modules\API\Suppliers\Base\Transformers\BaseHotelPricingTransformer;
use Modules\API\Suppliers\Contracts\Hotel\ContentV1\HotelContentV1SupplierRegistry;
use Modules\API\Suppliers\Contracts\Hotel\Search\HotelSupplierRegistry;
use Modules\API\Tools\FiberManager;
use Modules\API\Tools\MemoryLogger;
use Modules\API\Tools\PricingDtoTools;
use Modules\API\Tools\PricingRulesTools;
use Modules\Enums\SupplierNameEnum;
use Modules\HotelContentRepository\Models\Hotel;
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
        private readonly HotelSupplierRegistry $supplierRegistry,
        private readonly BaseHotelPricingTransformer $baseHotelPricingTransformer,
        private readonly PricingDtoTools $pricingDtoTools,
        private readonly SearchInspectorController $apiInspector,
        private readonly EnrichmentWeight $propsWeight,
        private readonly PricingRulesTools $pricingRulesService,
        private readonly HotelContentV1SupplierRegistry $contentRegistry,
    ) {
        $this->start();
    }

    public function search(Request $request): JsonResponse
    {
        return $this->sendError('deprecate, use v1', 'failed');
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

                    $clientResponse[$supplierName] = $this->contentRegistry->get(SupplierNameEnum::from($supplierName))->getResults($giataCodes);
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

                    $rawGiataIds = $this->supplierRegistry->get($supplier)->preSearchData($filters, 'price') ?? [];

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

                        $fiberManager->add($fiberKey, function () use ($supplier, $currentFilters, $searchInspector, $rawGiataIds) {
                            return $this->supplierRegistry->get($supplier)->price($currentFilters, $searchInspector, $rawGiataIds);
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

                    $result = $this->supplierRegistry
                        ->get($supplierName)
                        ->processPriceResponse(
                            $supplierResponse,
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

                MemoryLogger::log('processPriceResponse');

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

                $res = $request->input('supplier_data') == 'true' ? $content : $clientContent;
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

        foreach ($result['results'] as $supplier => $data) {
            $output = Arr::get($result, "results.$supplier", []);
            $value = $this->filterByPrice($output ?? [], $maxPriceFilter, $minPriceFilter);
            $result['results'][$supplier] = $value;
        }

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
