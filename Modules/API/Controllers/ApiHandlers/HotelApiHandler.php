<?php

namespace Modules\API\Controllers\ApiHandlers;

use App\Jobs\SaveBookingItems;
use App\Jobs\SaveSearchInspector;
use App\Models\GeneralConfiguration;
use App\Models\Supplier;
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

    private const PAGINATION_TO_RESULT = true;

    /**
     * @param ExpediaHotelController $expedia
     * @param IcePortalHotelController $icePortal
     * @param SearchInspectorController $apiInspector
     * @param ExpediaHotelPricingDto $ExpediaHotelPricingDto
     * @param ExpediaHotelContentDto $ExpediaHotelContentDto
     * @param IcePortalHotelContentDto $IcePortalHotelContentDto
     * @param IcePortalHotelContentDetailDto $HbsiHotelContentDetailDto
     * @param ExpediaHotelContentDetailDto $ExpediaHotelContentDetailDto
     * @param EnrichmentWeight $propsWeight
     * @param HbsiHotelController $hbsi
     * @param HbsiHotelPricingDto $HbsiHotelPricingDto
     * @param PricingRulesTools $pricingRulesService
     * @param HbsiService $hbsiService
     */
    public function __construct(
        private readonly ExpediaHotelController         $expedia = new ExpediaHotelController(),
        private readonly IcePortalHotelController       $icePortal = new IcePortalHotelController(),
        private readonly SearchInspectorController      $apiInspector = new SearchInspectorController(),
        private readonly ExpediaHotelPricingDto         $ExpediaHotelPricingDto = new ExpediaHotelPricingDto(),
        private readonly ExpediaHotelContentDto         $ExpediaHotelContentDto = new ExpediaHotelContentDto(),
        private readonly IcePortalHotelContentDto       $IcePortalHotelContentDto = new IcePortalHotelContentDto(),
        private readonly IcePortalHotelContentDetailDto $HbsiHotelContentDetailDto = new IcePortalHotelContentDetailDto(),
        private readonly ExpediaHotelContentDetailDto   $ExpediaHotelContentDetailDto = new ExpediaHotelContentDetailDto(),
        private readonly EnrichmentWeight               $propsWeight = new EnrichmentWeight(),
        private readonly HbsiHotelController            $hbsi = new HbsiHotelController(),
        private readonly HbsiHotelPricingDto            $HbsiHotelPricingDto = new HbsiHotelPricingDto(),
        private readonly PricingRulesTools              $pricingRulesService = new PricingRulesTools(),
        private readonly HbsiService                    $hbsiService = new HbsiService(),

    )
    {
        $this->start();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $filters = $request->all();
            $supplierNames = explode(', ', (GeneralConfiguration::pluck('content_supplier')->toArray()[0] ?? 'Expedia'));
            $keyPricingSearch = $request->type . ':contentSearch:' . http_build_query(Arr::dot($filters));
            $tag = 'content_search';
            $keyContent = $keyPricingSearch . ':content';
            $keyClientContent = $keyPricingSearch . ':clientContent';
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
                    if (isset($request->supplier) && $request->supplier != $supplierName) continue;

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
                        Log::debug('HotelApiHandler | search | ' . $supplierName . ' | runtime ' . $this->duration($supplierName));
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
                    'total_pages' => max($totalPages),
                    'query' => $filters,
                    'results' => $clientResponse,
                ];

                $taggedCache->put($keyContent, $content, now()->addMinutes(60));
                $taggedCache->put($keyClientContent, $clientContent, now()->addMinutes(60));
            }

            if ($request->input('supplier_data') == 'true') $res = $content;
            else $res = $clientContent;

            $res['count'] = $res['count'][0];
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
            Log::error('HotelApiHandler | search' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return $this->sendError($e->getMessage(), 'failed');
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function detail(Request $request): JsonResponse
    {
        try {
            $supplierNames = explode(', ', GeneralConfiguration::pluck('content_supplier')->toArray()[0]);
            $keyPricingSearch = $request->type . ':contentDetail:' . http_build_query(Arr::dot($request->all()));

            if (Cache::has($keyPricingSearch . ':dataResponse') && Cache::has($keyPricingSearch . ':clientResponse')) {

                $dataResponse = Cache::get($keyPricingSearch . ':dataResponse');
                $clientResponse = Cache::get($keyPricingSearch . ':clientResponse');
            } else {

                $dataResponse = [];
                $clientResponse = [];
                foreach ($supplierNames as $supplierName) {
                    if (isset($request->supplier) && $request->supplier != $supplierName) continue;

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
                            ? $this->HbsiHotelContentDetailDto->HbsiToContentDetailResponse((object)$data, $request->input('property_id'))
                            : [];
                    }
                }

                Cache::put($keyPricingSearch . ':dataResponse', $dataResponse, now()->addMinutes(60));
                Cache::put($keyPricingSearch . ':clientResponse', $clientResponse, now()->addMinutes(60));
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

            return $this->sendResponse(['results' => $results, 'content_supplier' => $contentSupplier], 'success');
        } catch (Exception|NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            Log::error('HotelApiHandler ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return $this->sendError($e->getMessage(), 'failed');
        }
    }

    /**
     * @param Request $request
     * @param array $suppliers
     * @return JsonResponse
     * @throws Throwable
     */
    public function price(Request $request, array $suppliers): JsonResponse
    {
        try {
            $filters = $request->all();

            if (self::PAGINATION_TO_RESULT) {
                // this will set up the receipt of all records from the vendors
                unset($filters['page']);
                unset($filters['results_per_page']);
            }

            $token = $request->bearerToken();
            $keyPricingSearch = $request->type . ':pricingSearch:' . http_build_query(Arr::dot($filters)) . ':' . $token;
            $tag = 'pricing_search';
            $taggedCache = Cache::tags($tag);

            if ($taggedCache->has($keyPricingSearch . ':result')) {
                $res = $taggedCache->get($keyPricingSearch . ':result');
            } else {

                if (!isset($filters['rating']))
                    $filters['rating'] = GeneralConfiguration::latest()->first()->star_ratings ?? 3;

                $search_id = (string)Str::uuid();

                $st = microtime(true);
                $pricingRules = $this->pricingRulesService->rules($filters);
                Log::info('HotelApiHandler | price | pricingRulesService ' . (microtime(true) - $st) . 's');

                $dataResponse = $clientResponse = $fibers = $bookingItems = $dataOriginal = $totalPages = [];
                $countResponse = $countClientResponse = 0;

                /**
                 * Fiber is used to collect all the promises first,
                 * and then together execute them asynchronously
                 */
                foreach ($suppliers as $supplierId) {
                    $supplier = Supplier::find($supplierId)?->name;
                    if ($request->supplier) {
                        $supplierQuery = explode(',', $request->supplier);
                        if (!in_array($supplier, $supplierQuery)) continue;
                    }
                    $fibers[$supplier] = new Fiber(function () use ($supplier, $filters, $search_id, $pricingRules) {
                        $supplierResponse = match (SupplierNameEnum::from($supplier)) {
                            SupplierNameEnum::EXPEDIA => $this->expedia->price($filters),
                            SupplierNameEnum::HBSI => $this->hbsi->price($filters),
                            default => throw new Exception("Unknown supplier: $supplier")
                        };

                        return $this->handlePriceSupplier($supplierResponse, $supplier, $filters, $search_id, $pricingRules);
                    });
                }

                /**
                 * Collecting all the promises
                 * Each Supplier's request may contain a group of promises/requests
                 * Running Fiber returns a value, which can be either a promise or an array of promises.
                 * If it is an array, the code iterates over each promise in the array and adds it to the $promises array using a key in the format i_j,
                 * where i is the Fiber index and j is the index of the promises in the array.
                 * If the returned value is not an array, it is assumed to be a promise and is added to the $promises array using the key corresponding to the fiber index.
                 */
                $promises = [];
                foreach ($fibers as $i => $fiber) {
                    if (!$fiber->isStarted()) {
                        $startFiber = $fiber->start();
                        if (is_array($startFiber))
                            foreach ($startFiber as $j => $promise) {
                                $promises[$i . '_' . $j] = $promise;
                            }
                        else $promises[$i] = $startFiber;
                    }
                }

                $st = microtime(true);
                /** Running the promises asynchronously */
                $resolvedResponses = Promise\Utils::settle($promises)->wait();

                /**
                 * As a result of this code, the $resume array will contain all values from $resolvedResponses,
                 * but they will be grouped by the first part of the source keys. If the source key contained two parts,
                 * the corresponding values will be grouped in a subarray.
                 */
                $resume = [];
                foreach ($resolvedResponses as $key => $resolvedResponse) {
                    $arrKey = explode('_', $key);
                    $i = $arrKey[0];

                    if (count($arrKey) === 2) $resume[$i][] = $resolvedResponse;
                    else $resume[$i] = $resolvedResponse;
                }
                Log::info('HotelApiHandler | price | asyncResponses ' . (microtime(true) - $st) . 's');

                /** Results processing */
                foreach ($fibers as $i => $fiber) {
                    if ($fiber->isSuspended()) {
                        $fiber->resume($resume[$i] ?? null);
                    }
                    if ($fiber->isTerminated()) {
                        $result = $fiber->getReturn();

                        if ($error = Arr::get($result, 'error'))
                        {
                            return $this->sendError($error, 'failed');
                        }

                        $dataResponse = array_merge($dataResponse, $result['dataResponse']);
                        $clientResponse = array_merge($clientResponse, $result['clientResponse']);
                        $totalPages = array_merge($totalPages, $result['totalPages']);
                        $countResponse += $result['countResponse'];
                        $countClientResponse += $result['countClientResponse'];
                        $bookingItems = array_merge($bookingItems, $result['bookingItems'] ?? []);
                        $dataOriginal = array_merge($dataOriginal, $result['dataOriginal'] ?? []);
                    }
                }

                /** Enrichment Property Weighting */
                $clientResponse = $this->propsWeight->enrichmentPricing($clientResponse, 'hotel');

                $content = ['count' => $countResponse, 'query' => $filters, 'results' => $dataResponse];
                $clientContent = [
                    'count' => $countClientResponse,
                    'total_pages' => max($totalPages),
                    'query' => $filters,
                    'results' => $clientResponse
                ];

                /** Save data to Inspector */
                Log::info('HotelApiHandler | price | SaveSearchInspector | start');
                SaveSearchInspector::dispatch([
                    $search_id, $filters, $dataOriginal, $content, $clientContent, $suppliers, 'price', 'hotel',
                ]);
                Log::info('HotelApiHandler | price | SaveSearchInspector | end');

                if (!empty($bookingItems)) {
                    foreach ($bookingItems as $items) {
                        SaveBookingItems::dispatch($items);
                    }
                }

                if ($request->input('supplier_data') == 'true') $res = $content;
                else $res = $clientContent;

                $res['search_id'] = $search_id;

                $taggedCache->put($keyPricingSearch . ':result', $res, now()->addMinutes(60));
            }

            if (self::PAGINATION_TO_RESULT) {
//                $res = $this->paginate($res, $request->input('page', 1), $request->input('results_per_page', 10));
                $res = $this->combinedAndPaginate($res, $request->input('page', 1), $request->input('results_per_page', 10));
            }

            return $this->sendResponse($res, 'success');
        } catch (Exception|NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            Log::error('HotelApiHandler ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return $this->sendError($e->getMessage(), 'failed');
        }
    }

    /**
     * Paginate the given results.
     *
     * @param array $results The results to paginate.
     * @param int $page The current page number.
     * @param int $resultsPerPage The number of results per page.
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

    public function combinedAndPaginate(array $results, int $page, int $resultsPerPage): array
    {
        // Merge all supplier results into one array
        $mergedResults = [];
        foreach ($results['results'] as $supplierResults) {
            $mergedResults = array_merge($mergedResults, $supplierResults);
        }

        // Sort the merged results by 'weight' and 'lowest_priced_room_group'
        usort($mergedResults, function ($a, $b) {
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
        $results['count_per_page'] =count($pagedResults);

        return $results;
    }

    /**
     * @throws Throwable
     */
    private function handlePriceSupplier($supplierResponse, string $supplierName, array $filters, string $search_id, array $pricingRules): array
    {
        $dataResponse = [];
        $clientResponse = [];
        $totalPages = [];
        $countResponse = 0;
        $countClientResponse = 0;

        if (SupplierNameEnum::from($supplierName) === SupplierNameEnum::EXPEDIA) {

            $expediaResponse = $supplierResponse;

            $dataResponse[$supplierName] = $expediaResponse['array'];
            $dataOriginal[$supplierName] = $expediaResponse['original'];

            $st = microtime(true);
            $dtoData = $this->ExpediaHotelPricingDto->ExpediaToHotelResponse($expediaResponse['array'], $filters, $search_id, $pricingRules);
            $bookingItems[$supplierName] = $dtoData['bookingItems'];
            $clientResponse[$supplierName] = $dtoData['response'];
            Log::info('HotelApiHandler | price | DTO ExpediaToHotelResponse ' . (microtime(true) - $st) . 's');

            $countResponse += count($expediaResponse);
            $totalPages[$supplierName] = $expediaResponse['total_pages'] ?? 0;
            $countClientResponse += count($clientResponse[$supplierName]);
        }

        if (SupplierNameEnum::from($supplierName) === SupplierNameEnum::HBSI) {

            $hbsiResponse = $supplierResponse;

            $dataResponse[$supplierName] = $hbsiResponse['array'];
            $dataOriginal[$supplierName] = $hbsiResponse['original'];

            $st = microtime(true);
            $dtoData = $this->HbsiHotelPricingDto->HbsiToHotelResponse($hbsiResponse['array'], $filters, $search_id, $pricingRules);

            /** Enrichment Room Combinations */
            $countRooms = count($filters['occupancy']);
            if ($countRooms > 1) {
                $clientResponse[$supplierName] = $this->hbsiService->enrichmentRoomCombinations($dtoData['response'], $filters);
            }
            else $clientResponse[$supplierName] = $dtoData['response'];
            $bookingItems[$supplierName] = $dtoData['bookingItems'];

            Log::info('HotelApiHandler | price | DTO hbsiResponse ' . (microtime(true) - $st) . 's');

            $countResponse += count($hbsiResponse['array']);
            $totalPages[$supplierName] = $hbsiResponse['total_pages'] ?? 0;
            $countClientResponse += count($clientResponse[$supplierName]);
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
