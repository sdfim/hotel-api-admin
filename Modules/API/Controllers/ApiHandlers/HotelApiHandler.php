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

            if (Cache::has($keyPricingSearch . ':content') && Cache::has($keyPricingSearch . ':clientContent')) {
                $content = Cache::get($keyPricingSearch . ':content');
                $clientContent = Cache::get($keyPricingSearch . ':clientContent');
            } else {
                $dataResponse = [];
                $clientResponse = [];
                $count = [];
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
                    'query' => $filters,
                    'results' => $clientResponse,
                ];

                Cache::put($keyPricingSearch . ':content', $content, now()->addMinutes(60));
                Cache::put($keyPricingSearch . ':clientContent', $clientContent, now()->addMinutes(60));
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

            $keyPricingSearch = $request->type . ':pricingSearch:' . http_build_query(Arr::dot($filters));

            if (Cache::has($keyPricingSearch . ':result')) {
                $res = Cache::get($keyPricingSearch . ':result');
            } else {

                if (!isset($filters['rating']))
                    $filters['rating'] = GeneralConfiguration::latest()->first()->star_ratings ?? 3;

                $search_id = (string)Str::uuid();

                $st = microtime(true);
                $pricingRules = $this->pricingRulesService->rules($filters);
                Log::info('HotelApiHandler | price | pricingRulesService ' . (microtime(true) - $st) . 's');

                $dataResponse = $clientResponse = $fibers = $bookingItems = $dataOriginal = [];
                $countResponse = $countClientResponse = 0;

                /**
                 * Fiber is used to collect all the promises first,
                 * and then together execute them asynchronously
                 */
                foreach ($suppliers as $supplierId) {
                    $supplier = Supplier::find($supplierId)?->name;
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
                        $dataResponse = array_merge($dataResponse, $result['dataResponse']);
                        $clientResponse = array_merge($clientResponse, $result['clientResponse']);
                        $countResponse += $result['countResponse'];
                        $countClientResponse += $result['countClientResponse'];
                        $bookingItems = array_merge($bookingItems, $result['bookingItems'] ?? []);
                        $dataOriginal = array_merge($dataOriginal, $result['dataOriginal'] ?? []);
                    }
                }

                /** Enrichment Property Weighting */
                $clientResponse = $this->propsWeight->enrichmentPricing($clientResponse, 'hotel');

                $content = ['count' => $countResponse, 'query' => $filters, 'results' => $dataResponse];
                $clientContent = ['count' => $countClientResponse, 'query' => $filters, 'results' => $clientResponse];

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

                Cache::put($keyPricingSearch . ':result', $res, now()->addMinutes(60));
            }

            return $this->sendResponse($res, 'success');
        } catch (Exception|NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            Log::error('HotelApiHandler ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return $this->sendError($e->getMessage(), 'failed');
        }
    }

    /**
     * @throws Throwable
     */
    private function handlePriceSupplier($supplierResponse, string $supplierName, array $filters, string $search_id, array $pricingRules): array
    {
        $dataResponse = [];
        $clientResponse = [];
        $countResponse = 0;
        $countClientResponse = 0;

        if (SupplierNameEnum::from($supplierName) === SupplierNameEnum::EXPEDIA) {

            $expediaResponse = $supplierResponse;
            $dataResponse[$supplierName] = $expediaResponse;

            $st = microtime(true);
            $dtoData = $this->ExpediaHotelPricingDto->ExpediaToHotelResponse($expediaResponse, $filters, $search_id, $pricingRules);
            $bookingItems[$supplierName] = $dtoData['bookingItems'];
            $clientResponse[$supplierName] = $dtoData['response'];
            Log::info('HotelApiHandler | price | DTO ExpediaToHotelResponse ' . (microtime(true) - $st) . 's');

            $countResponse += count($expediaResponse);
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
                $clientResponse[$supplierName] = $this->hbsiService->enrichmentRoomCombinations($dtoData['response'], $filters, $search_id);
            }
            else $clientResponse[$supplierName] = $dtoData['response'];
            $bookingItems[$supplierName] = $dtoData['bookingItems'];

            Log::info('HotelApiHandler | price | DTO hbsiResponse ' . (microtime(true) - $st) . 's');

            $countResponse += count($hbsiResponse['array']);
            $countClientResponse += count($clientResponse[$supplierName]);
        }

        return [
            'dataResponse' => $dataResponse,
            'clientResponse' => $clientResponse,
            'countResponse' => $countResponse,
            'countClientResponse' => $countClientResponse,
            'bookingItems' => $bookingItems ?? [],
            'dataOriginal' => $dataOriginal ?? [],
        ];

    }
}
