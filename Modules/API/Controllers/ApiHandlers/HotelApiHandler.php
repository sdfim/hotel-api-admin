<?php

namespace Modules\API\Controllers\ApiHandlers;

use App\Jobs\SaveBookingItems;
use App\Jobs\SaveSearchInspectorByCacheKey;
use App\Models\GeneralConfiguration;
use App\Models\Supplier;
use App\Repositories\ApiSearchInspectorRepository;
use App\Traits\Timer;
use Exception;
use Fiber;
use GuzzleHttp\Promise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\API\BaseController;
use Modules\API\Controllers\ApiHandlerInterface;
use Modules\API\Controllers\ApiHandlers\ContentSuppliers\ExpediaHotelController;
use Modules\API\Controllers\ApiHandlers\ContentSuppliers\IcePortalHotelController;
use Modules\API\Controllers\ApiHandlers\PricingSuppliers\HbsiHotelController;
use Modules\API\PropertyWeighting\EnrichmentWeight;
use Modules\API\Suppliers\DTO\Expedia\ExpediaHotelContentDetailDto;
use Modules\API\Suppliers\DTO\Expedia\ExpediaHotelContentDto;
use Modules\API\Suppliers\DTO\Expedia\ExpediaHotelPricingDto;
use Modules\API\Suppliers\DTO\HBSI\HbsiHotelPricingDto;
use Modules\API\Suppliers\DTO\IcePortal\IcePortalHotelContentDetailDto;
use Modules\API\Suppliers\DTO\IcePortal\IcePortalHotelContentDto;
use Modules\API\Suppliers\HbsiSupplier\HbsiService;
use Modules\API\Tools\PricingDtoTools;
use Modules\API\Tools\PricingRulesTools;
use Modules\Enums\SupplierNameEnum;
use Modules\Inspector\SearchInspectorController;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;
/**
 * @OA\PathItem(
 * path="/api/content",
 * )
 */
class HotelApiHandler extends BaseController implements ApiHandlerInterface
{
    use Timer;

    //TODO: TEMPORARILY REDUCED TO 0.5 TO AVOID CACHE CLEAR ISSUES IN Modules/API/Tools/ClearSearchCacheByBookingItemsTools.php
    public const TTL = 60;

    private const PAGINATION_TO_RESULT = true;

    public function __construct(
        private readonly HbsiHotelPricingDto            $HbsiHotelPricingDto,
        private readonly HbsiHotelController            $hbsi,
        private readonly PricingDtoTools                $pricingDtoTools = new PricingDtoTools(),
        private readonly ExpediaHotelController         $expedia = new ExpediaHotelController(),
        private readonly IcePortalHotelController       $icePortal = new IcePortalHotelController(),
        private readonly SearchInspectorController      $apiInspector = new SearchInspectorController(),
        private readonly ExpediaHotelPricingDto         $ExpediaHotelPricingDto = new ExpediaHotelPricingDto(),
        private readonly ExpediaHotelContentDto         $ExpediaHotelContentDto = new ExpediaHotelContentDto(),
        private readonly IcePortalHotelContentDto       $IcePortalHotelContentDto = new IcePortalHotelContentDto(),
        private readonly IcePortalHotelContentDetailDto $HbsiHotelContentDetailDto = new IcePortalHotelContentDetailDto(),
        private readonly ExpediaHotelContentDetailDto   $ExpediaHotelContentDetailDto = new ExpediaHotelContentDetailDto(),
        private readonly EnrichmentWeight               $propsWeight = new EnrichmentWeight(),
        private readonly PricingRulesTools              $pricingRulesService = new PricingRulesTools(),
        private readonly HbsiService                    $hbsiService = new HbsiService(),

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
                $supplierContentDto = null;

                foreach ($supplierNames as $supplierName) {
                    if (isset($request->supplier) && $request->supplier != $supplierName) {
                        continue;
                    }

                    $this->start($supplierName);

                    if (SupplierNameEnum::from($supplierName) === SupplierNameEnum::EXPEDIA) {
                        $supplierContent = $this->expedia;
                        $supplierContentDto = $this->ExpediaHotelContentDto;
                    }
                    if (SupplierNameEnum::from($supplierName) === SupplierNameEnum::ICE_PORTAL) {
                        $supplierContent = $this->icePortal;
                        $supplierContentDto = $this->IcePortalHotelContentDto;
                    }

                    if ($supplierContent === null || $supplierContentDto === null) {
                        throw new Exception('Supplier content or DTO is not set');
                    } else {
                        $supplierData = $supplierContent->search($filters);
                        $data = $supplierData['results'];
                        $count[] = $supplierData['count'];
                        $totalPages[] = $supplierData['total_pages'] ?? 0;
                        $dataResponse[$supplierName] = $data;
                        $clientResponse[$supplierName] = $supplierContentDto->SupplierToContentSearchResponse($data);
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
                    'total_pages' => !empty($totalPages) ? max($totalPages) : 0,
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
            $roomTypeCodes = $request->input('room_type_codes') ?? [];
            $keyDetail = $request->type.':contentDetail:'.http_build_query(Arr::dot($request->all()));

            if (Cache::has($keyDetail.':dataResponse') && Cache::has($keyDetail.':clientResponse')) {

                $dataResponse = Cache::get($keyDetail.':dataResponse');
                $clientResponse = Cache::get($keyDetail.':clientResponse');

            } else {

                $dataResponse = [];
                $clientResponse = [];
                foreach ($supplierNames as $supplierName) {
                    if (isset($request->supplier) && $request->supplier != $supplierName) {
                        continue;
                    }

                    if (SupplierNameEnum::from($supplierName) === SupplierNameEnum::EXPEDIA) {
                        $data = $this->expedia->detail($request);
                        $dataResponse[$supplierName] = $data;
                        $clientResponse[$supplierName] = count($data) > 0
                            ? $this->ExpediaHotelContentDetailDto->ExpediaToContentDetailResponse($data->first(), $request->input('property_id'))
                            : [];
                    }
                    if (SupplierNameEnum::from($supplierName) === SupplierNameEnum::ICE_PORTAL) {
                        $data = $this->icePortal->detail($request);
                        $dataResponse[$supplierName] = $data;
                        $clientResponse[$supplierName] = count($data) > 0
                            ? $this->HbsiHotelContentDetailDto->HbsiToContentDetailResponse((object) $data, $request->input('property_id'), $roomTypeCodes)
                            : [];
                    }
                }

                Cache::put($keyDetail.':dataResponse', $dataResponse, now()->addMinutes(self::TTL));
                Cache::put($keyDetail.':clientResponse', $clientResponse, now()->addMinutes(self::TTL));
            }

            if ($request->input('supplier_data') == 'true') {
                $results = $dataResponse;
            } else {
                $results = $clientResponse;
            }

            if (count($supplierNames) > 1) {
                $contentSupplier = $request->supplier ?? 'Expedia';
                $results['general'] = $results[$contentSupplier] ?? [];
            } else {
                $results['general'] = $results[$supplierNames[0]] ?? [];
                unset($results[$supplierNames[0]]);
                $contentSupplier = $supplierNames[0];
            }

            $end = microtime(true);
            $executionTime = ($end - $start) * 1000;
            Log::info('HotelApiHandler _ detail _ Execution time: ' . $executionTime . ' ms');

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
    public function price(Request $request, array $suppliers): JsonResponse
    {
        Log::info('Memory usage start: ' . memory_get_usage() / 1024 / 1024 . ' MB');
        $stp = microtime(true);

        try {
            $sts = microtime(true);
            $filters = $request->all();

            $supplierNames = $request->supplier ? explode(',', $request->supplier) : [];
            $supplierIds = Supplier::whereIn('name', $supplierNames)->pluck('id')->toArray();
            if (! empty($supplierIds)) {
                $suppliers = $supplierIds;
            }

            if (self::PAGINATION_TO_RESULT) {
                // this will set up the receipt of all records from the vendors
                unset($filters['page']);
                unset($filters['results_per_page']);
            }

            $token = $request->bearerToken();
            $keyPricingSearch = $request->type.':pricingSearch:'.http_build_query(Arr::dot($filters)).':'.$token;
            $tag = 'pricing_search';
            $taggedCache = Cache::tags($tag);


            Log::info('HotelApiHandler _ price _ preparation '.(microtime(true) - $sts).' seconds');

            if ($taggedCache->has($keyPricingSearch.':result')) {
                $res = $taggedCache->get($keyPricingSearch.':result');
            }
            else {
                $sts = microtime(true);
                if (! isset($filters['rating'])) {
                    $filters['rating'] = GeneralConfiguration::latest()->first()->star_ratings ?? 3;
                }

                $search_id = (string) Str::uuid();
                $searchInspector = ApiSearchInspectorRepository::newSearchInspector([$search_id, $filters, $suppliers, 'price', 'hotel']);

                $st = microtime(true);
                $pricingRules = $this->pricingRulesService->rules($filters);
                Log::info('HotelApiHandler _ price _ pricingRulesService '.(microtime(true) - $st).' seconds');

                $dataResponse = $clientResponse = $fibers = $bookingItems = $dataOriginal = $totalPages = [];
                $countResponse = $countClientResponse = 0;

                $filters['query_package'] = $filters['query_package'] ?? 'both';

                Log::info('HotelApiHandler _ price _ start '.(microtime(true) - $sts).' seconds');


                /**
                 * Fiber is used to collect all the promises first,
                 * and then together execute them asynchronously
                 */
                foreach ($suppliers as $supplierId) {
                    $sts = microtime(true);

                    $supplier = Supplier::find($supplierId)?->name;
                    if ($request->supplier) {
                        $supplierQuery = explode(',', $request->supplier);
                        if (! in_array($supplier, $supplierQuery)) {
                            continue;
                        }
                    }

                    /**
                     * The $optionsQueries array contains the possible values for the 'query_package' key.
                     * If the 'query_package' key is set to 'both', the array will contain both 'hotel_only' and 'hotel_package'.
                     * This for Expedia supplier, which can return both types of results.
                     * standalone -> hotel_only; package -> hotel_package
                     */
                    if ($supplier === SupplierNameEnum::EXPEDIA->value) {
                        $optionsQueries = $filters['query_package'] === 'both' ? ['standalone', 'package'] : [$filters['query_package']];
                    } else $optionsQueries = ['any'];

                    $preSearchData = match (SupplierNameEnum::from($supplier)) {
                        SupplierNameEnum::EXPEDIA => $this->expedia->preSearchData($filters, 'price'),
                        SupplierNameEnum::HBSI => $this->hbsi->preSearchData($filters),
                        default => throw new Exception("Unknown supplier: $supplier")
                    };

                    foreach ($optionsQueries as $optionsQuery) {
                        $fiberKey = $supplier . '_' . $optionsQuery;

                        $currentFilters = $filters;
                        $currentFilters['query_package'] = $optionsQuery;

                        $fibers[$fiberKey] = new Fiber(function () use ($supplier, $currentFilters, $search_id, $pricingRules, $searchInspector, $preSearchData) {
                            $supplierResponse = match (SupplierNameEnum::from($supplier)) {
                                SupplierNameEnum::EXPEDIA => $this->expedia->price($currentFilters, $searchInspector, $preSearchData),
                                SupplierNameEnum::HBSI => $this->hbsi->price($currentFilters, $searchInspector, $preSearchData),
                                default => throw new Exception("Unknown supplier: $supplier")
                            };
                            $giataIds = [];
                            if (SupplierNameEnum::from($supplier) === SupplierNameEnum::HBSI) {
                                $giataIds = array_column(Arr::get($preSearchData, 'data', []), 'giata');
                            }

                            return $this->handlePriceSupplier($supplierResponse, $supplier, $currentFilters, $search_id, $pricingRules, $giataIds);
                        });
                    }

                    Log::info('HotelApiHandler _ price _ Fiber '.$supplier.' '.(microtime(true) - $sts).' seconds');
                }
                $sts = microtime(true);

                /**
                 * Collecting all the promises
                 * Each Supplier's request may contain a group of promises/requests
                 * Running Fiber returns a value, which can be either a promise or an array of promises.
                 * If it is an array, the code iterates over each promise in the array and adds it to the $promises array using a key in the format i_j,
                 * where i is the Fiber index and j is the index (chunk) of the promises in the array.
                 * If the returned value is not an array, it is assumed to be a promise and is added to the $promises array using the key corresponding to the fiber index.
                 */
                $promises = [];
                foreach ($fibers as $i => $fiber) {
                    if (! $fiber->isStarted()) {
                        $startFiber = $fiber->start();
                        if (is_array($startFiber)) {
                            foreach ($startFiber as $j => $promise) {
                                $promises[$i.'_'.$j] = $promise;
                            }
                        } else {
                            $promises[$i] = $startFiber;
                        }
                    }
                }

                $st = microtime(true);
                /** Running the promises asynchronously */
                $resolvedResponses = Promise\Utils::settle($promises)->wait();

                Log::info('Memory usage get promises: ' . memory_get_usage() / 1024 / 1024 . ' MB');

                /**
                 * As a result of this code, the $resume array will contain all values from $resolvedResponses,
                 * but they will be grouped by the first part of the source keys. If the source key contained two parts,
                 * the corresponding values will be grouped in a subarray.
                 */
                $resume = [];
                foreach ($resolvedResponses as $key => $resolvedResponse) {
                    $arrKey = explode('_', $key);
                    $supplierName = $arrKey[0];
                    $queryPackage = $arrKey[1];

                    if (count($arrKey) === 3) $resume[$supplierName][$queryPackage][] = $resolvedResponse;
                    else $resume[$supplierName][$queryPackage] = $resolvedResponse;
                }
                Log::info('HotelApiHandler _ price _ asyncResponses '.(microtime(true) - $sts).' seconds');
                $sts = microtime(true);

                Log::info('Memory usage before fibers Results processing: ' . memory_get_usage() / 1024 / 1024 . ' MB');
                Log::info('Peak memory usage before fibers Results processing: ' . memory_get_peak_usage() / 1024 / 1024 . ' MB');

                /** Results processing */
                foreach ($fibers as $fiber_key => $fiber) {
                    $arrKey = explode('_', $fiber_key);
                    $supplierName = $arrKey[0];
                    $queryPackage = $arrKey[1];

                    if ($fiber->isSuspended()) {
                        $fiber->resume($resume[$supplierName][$queryPackage] ?? null);
                    }
                    if ($fiber->isTerminated()) {
                        $result = $fiber->getReturn();

                        if ($error = Arr::get($result, 'error')) {
                            return $this->sendError($error, 'failed');
                        }

                        if (!str_contains($fiber_key, SupplierNameEnum::EXPEDIA->value)) $fiber_key = $supplierName;

                        $dataResponse[$fiber_key] = $result['dataResponse'][$supplierName];
                        $clientResponse[$fiber_key] = $result['clientResponse'][$supplierName];
                        $totalPages[$fiber_key] = $result['totalPages'][$supplierName];
                        $bookingItems[$fiber_key] = $result['bookingItems'][$supplierName] ?? [];
                        $dataOriginal[$fiber_key] = $result['dataOriginal'][$supplierName] ?? [];
                        $countResponse += $result['countResponse'];
                        $countClientResponse += $result['countClientResponse'];
                    }
                }

                /** Expedia RS aggregation If the 'query_package' key is set to 'both' */
                if ($filters['query_package'] === 'both' && isset($clientResponse['Expedia_standalone'], $clientResponse['Expedia_package'])) {
                    $clientResponse['Expedia_both'] = $this->pricingDtoTools->mergeHotelData($clientResponse['Expedia_standalone'], $clientResponse['Expedia_package']);
                    unset($clientResponse['Expedia_standalone']);
                    unset($clientResponse['Expedia_package']);
                }

                Log::info('HotelApiHandler _ price _ Results processing '.(microtime(true) - $sts).' seconds');
                $sts = microtime(true);

                Log::info('Memory usage before Weighting: ' . memory_get_usage() / 1024 / 1024 . ' MB');
                Log::info('Peak memory usage before Weighting: ' . memory_get_peak_usage() / 1024 / 1024 . ' MB');

                /** Enrichment Property Weighting */
                $enrichClientResponse = $this->propsWeight->enrichmentPricing($clientResponse, 'hotel');

                Log::info('HotelApiHandler _ price _ Enrichment Property Weighting '.(microtime(true) - $sts).' seconds');
                $sts = microtime(true);

                if (!isset($filters['view_ids'])) unset($filters['ids']);
                $content = ['count' => $countResponse, 'query' => $filters, 'results' => $dataResponse];
                $clientContent = [
                    'count' => $countClientResponse,
                    'total_pages' => max($totalPages),
                    'query' => $filters,
                    'results' => $enrichClientResponse,
                ];

                Log::info('Memory usage after Weighting: ' . memory_get_usage() / 1024 / 1024 . ' MB');
                Log::info('Peak memory usage after Weighting: ' . memory_get_peak_usage() / 1024 / 1024 . ' MB');

                /** Save data to Inspector */
                $cacheKeys = [];
                foreach (['dataOriginal', 'content', 'clientContent'] as $variableName) {
                    $key = $variableName . '_' . uniqid();
                    $cacheKeys[$variableName] = $key;
                    Cache::put($key, json_encode($$variableName), now()->addMinutes(10));
                }
                // this approach is more memory-efficient.
                SaveSearchInspectorByCacheKey::dispatch($searchInspector, $cacheKeys);
                Log::info('HotelApiHandler _ price _ SaveSearchInspector ' . (microtime(true) - $sts) . ' seconds');
                $sts = microtime(true);

                Log::info('Memory usage after Save data to Inspector: ' . memory_get_usage() / 1024 / 1024 . ' MB');
                Log::info('Peak memory usage Save data to Inspector: ' . memory_get_peak_usage() / 1024 / 1024 . ' MB');

                if (!empty($bookingItems)) {
                    foreach ($bookingItems as $items) {
                        SaveBookingItems::dispatch($items);
                    }
                }

                Log::info('HotelApiHandler _ price _ SaveBookingItems ' . (microtime(true) - $sts) . ' seconds');

                if ($request->input('supplier_data') == 'true') {
                    $res = $content;
                } else {
                    $res = $clientContent;
                }

                $res['search_id'] = $search_id;

                $taggedCache->put($keyPricingSearch.':result', $res, now()->addMinutes(self::TTL));

                // This cache is used for actions to efficiently remove the cache for booked booking_items
                $taggedCache->put($search_id, $keyPricingSearch . ':result', now()->addMinutes(self::TTL));
                $arr_pricing_search = $taggedCache->get('arr_pricing_search');
                if (! is_array($arr_pricing_search)) {
                    $arr_pricing_search = [];
                }
                $arr_pricing_search[] = $search_id;
                $taggedCache->put('arr_pricing_search', $arr_pricing_search, now()->addMinutes(self::TTL));
            }

           $res = $this->applyFilters($res);
            Log::info('HotelApiHandler _ price _ end all time '.(microtime(true) - $stp).' seconds');

            if (self::PAGINATION_TO_RESULT) {
                //                $res = $this->paginate($res, $request->input('page', 1), $request->input('results_per_page', 10));
                $res = $this->combinedAndPaginate($res, $request->input('page', 1), $request->input('results_per_page', 50), $res['query'] );
            }

            return $this->sendResponse($res, 'success');
        } catch (Exception|NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            Log::error('HotelApiHandler '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return $this->sendError($e->getMessage(), 'failed');
        }
    }

    private function filterByPrice(array $target , $maxPriceFilter, $minPriceFilter): array
    {
        return collect($target)->filter(function($hotel) use($maxPriceFilter, $minPriceFilter){
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
        $value = $this->filterByPrice($output  ?? [], $maxPriceFilter,$minPriceFilter);
        $result['results']['Expedia_both'] = $value;

        $output = Arr::get($result, 'results.HBSI', []);
        $value = $this->filterByPrice($output  ?? [], $maxPriceFilter,$minPriceFilter);
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

        if( Arr::get($filters, 'order') === 'cheapest_price') {
            $mergedResults = collect($mergedResults)->sortBy('lowest_priced_room_group')->values()->toArray();
        }else{
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
    private function handlePriceSupplier($supplierResponse, string $supplierName, array $filters, string $search_id, array $pricingRules, array $giataIds): array
    {
        $dataResponse = [];
        $clientResponse = [];
        $totalPages = [];
        $bookingItems = [];
        $countResponse = 0;
        $countClientResponse = 0;

        if (SupplierNameEnum::from($supplierName) === SupplierNameEnum::EXPEDIA) {

            $expediaResponse = $supplierResponse;

            $dataResponse[$supplierName] = json_encode($expediaResponse['array']);
            $dataOriginal[$supplierName] = json_encode($expediaResponse['original']);

            $st = microtime(true);
            $dtoData = $this->ExpediaHotelPricingDto->ExpediaToHotelResponse($expediaResponse['array'], $filters, $search_id, $pricingRules);
            $bookingItems[$supplierName] = $dtoData['bookingItems'];
            $clientResponse[$supplierName] = $dtoData['response'];
            Log::info('HotelApiHandler _ price _ DTO ExpediaToHotelResponse '.(microtime(true) - $st).' seconds');

            $countResponse += count($expediaResponse);
            $totalPages[$supplierName] = $expediaResponse['total_pages'] ?? 0;
            $countClientResponse += count($dtoData['response']);
            unset($expediaResponse, $dtoData);
        }

        if (SupplierNameEnum::from($supplierName) === SupplierNameEnum::HBSI) {

            $hbsiResponse = $supplierResponse;

            $dataResponse[$supplierName] = $hbsiResponse['array'];
            $dataOriginal[$supplierName] = $hbsiResponse['original'];

            $st = microtime(true);
            $dtoData = $this->HbsiHotelPricingDto->HbsiToHotelResponse($hbsiResponse['array'], $filters, $search_id, $pricingRules, $giataIds);

            /** Enrichment Room Combinations */
            $countRooms = count($filters['occupancy']);
            if ($countRooms > 1) {
                $clientResponse[$supplierName] = $this->hbsiService->enrichmentRoomCombinations($dtoData['response'], $filters);
            } else {
                $clientResponse[$supplierName] = $dtoData['response'];
            }
            $bookingItems[$supplierName] = $dtoData['bookingItems'];

            Log::info('HotelApiHandler _ price _ DTO hbsiResponse '.(microtime(true) - $st).' seconds');

            $countResponse += count($hbsiResponse['array']);
            $totalPages[$supplierName] = $hbsiResponse['total_pages'] ?? 0;
            $countClientResponse += count($clientResponse[$supplierName]);

            unset($hbsiResponse, $dtoData);
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
}
