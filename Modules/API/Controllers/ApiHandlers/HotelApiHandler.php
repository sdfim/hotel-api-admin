<?php

namespace Modules\API\Controllers\ApiHandlers;

use App\Jobs\SaveBookingItems;
use App\Jobs\SaveSearchInspector;
use App\Models\GeneralConfiguration;
use App\Models\Supplier;
use App\Traits\Timer;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\API\BaseController;
use Modules\API\Controllers\ApiHandlerInterface;
use Modules\API\Controllers\ApiHandlers\ContentSuppliers\ExpediaHotelController;
use Modules\API\Controllers\ApiHandlers\ContentSuppliers\IcePortalHotelController;
use Modules\API\Controllers\ApiHandlers\PricingSuppliers\HbsiHotelController;
use Modules\API\PropertyWeighting\EnrichmentWeight;
use Modules\API\Requests\PriceHotelRequest;
use Modules\API\Requests\SearchHotelRequest;
use Modules\API\Suppliers\DTO\Expedia\ExpediaHotelContentDetailDto;
use Modules\API\Suppliers\DTO\Expedia\ExpediaHotelContentDto;
use Modules\API\Suppliers\DTO\Expedia\ExpediaHotelPricingDto;
use Modules\API\Suppliers\DTO\HBSI\HbsiHotelPricingDto;
use Modules\API\Suppliers\DTO\IcePortal\IcePortalHotelContentDetailDto;
use Modules\API\Suppliers\DTO\IcePortal\IcePortalHotelContentDto;
use Modules\Enums\SupplierNameEnum;
use Modules\Inspector\SearchInspectorController;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

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
    )
    {
        $this->start();
    }

    /*
     * @param Request $request
     * @return JsonResponse
     */

    /**
     * @OA\Post(
     *   tags={"Content API"},
     *   path="/api/content/search",
     *   summary="Search Hotels",
     *   description="Search for hotels by destination or coordinates.",
     *
     *   @OA\RequestBody(
     *     description="JSON object containing the details of the reservation.",
     *     required=true,
     *
     *     @OA\JsonContent(
     *       oneOf={
     *
     *            @OA\Schema(ref="#/components/schemas/ContentSearchRequestDestination"),
     *            @OA\Schema(ref="#/components/schemas/ContentSearchRequestCoordinates"),
     *            @OA\Schema(ref="#/components/schemas/ContentSearchRequestSupplierHotelName"),
     *         },
     *       examples={
     *           "searchByDestination": @OA\Schema(ref="#/components/examples/ContentSearchRequestDestination", example="ContentSearchRequestDestination"),
     *           "searchByCoordinates": @OA\Schema(ref="#/components/examples/ContentSearchRequestCoordinates", example="ContentSearchRequestCoordinates"),
     *           "searchBySupplierHotelName": @OA\Schema(ref="#/components/examples/ContentSearchRequestSupplierHotelName", example="ContentSearchRequestSupplierHotelName"),
     *       },
     *     ),
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/ContentSearchResponse",
     *       examples={
     *       "searchByCoordinates": @OA\Schema(ref="#/components/examples/ContentSearchResponse", example="ContentSearchResponse"),
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BadRequestResponse",
     *       examples={
     *       "example1": @OA\Schema(ref="#/components/examples/BadRequestResponse", example="BadRequestResponse"),
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse",
     *       examples={
     *       "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse"),
     *       }
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * )
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $filters = $request->all();
            $supplierNames = explode(', ', (GeneralConfiguration::pluck('content_supplier')->toArray()[0] ?? 'Expedia'));
            $keyPricingSearch = request()->get('type') . ':contentSearch:' . http_build_query(Arr::dot($filters));

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

                // enrichment Property Weighting
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

            return $this->sendError(['error' => $e->getMessage()], 'failed');
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    /**
     * @OA\Get(
     *   tags={"Content API"},
     *   path="/api/content/detail",
     *   summary="Delail Hotels",
     *   description="Get detailed information about a hotel.",
     *
     *    @OA\Parameter(
     *      name="type",
     *      in="query",
     *      required=true,
     *      description="Type of content to search (e.g., 'hotel').",
     *
     *      @OA\Schema(
     *        type="string",
     *        example="hotel"
     *        )
     *    ),
     *
     *    @OA\Parameter(
     *      name="property_id",
     *    in="query",
     *    required=true,
     *    description="Giata ID of the property to get details for (e.g., 98736411).",
     *
     *   	@OA\Schema(
     *      type="integer",
     *      example=98736411
     *    )
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/ContentDetailResponse",
     *       examples={
     *       "example1": @OA\Schema(ref="#/components/examples/ContentDetailResponse", example="ContentDetailResponse"),
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BadRequestResponse",
     *       examples={
     *       "example1": @OA\Schema(ref="#/components/examples/BadRequestResponse", example="BadRequestResponse"),
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse",
     *       examples={
     *       "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse"),
     *       }
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * )
     */
    public function detail(Request $request): JsonResponse
    {
        try {
            $supplierNames = explode(', ', GeneralConfiguration::pluck('content_supplier')->toArray()[0]);
            $keyPricingSearch = request()->get('type') . ':contentDetail:' . http_build_query(Arr::dot($request->all()));

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

            return $this->sendError(['error' => $e->getMessage()], 'failed');
        }
    }

    /*
     * @param Request $request
     * @return JsonResponse
     */

    /**
     * @OA\Post(
     *   tags={"Pricing API"},
     *   path="/api/pricing/search",
     *   summary="Search Price Hotels",
     *   description="The **'/api/pricing/search'** endpoint, when used for hotel pricing, <br> is a critical part of a hotel booking API. <br> It enables users and developers to search for and obtain detailed pricing information related to hotel accommodations.",
     *
     *   @OA\RequestBody(
     *     description="JSON object containing the details of the reservation.",
     *     required=true,
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/PricingSearchRequest",
     *       examples={
     *           "NewYork": @OA\Schema(ref="#/components/examples/PricingSearchRequestNewYork", example="PricingSearchRequestNewYork"),
     *           "London": @OA\Schema(ref="#/components/examples/PricingSearchRequestLondon", example="PricingSearchRequestLondon"),
     *           "SupplierCurrency": @OA\Schema(ref="#/components/examples/PricingSearchRequestCurrencySupplier", example="PricingSearchRequestCurrencySupplier"),
     *       },
     *     ),
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/PricingSearchResponse",
     *         examples={
     *           "NewYork": @OA\Schema(ref="#/components/examples/PricingSearchResponseNewYork", example="PricingSearchResponseNewYork"),
     *           "London": @OA\Schema(ref="#/components/examples/PricingSearchResponseLondon", example="PricingSearchResponseLondon"),
     *       },
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BadRequestResponse",
     *       examples={
     *       "example1": @OA\Schema(ref="#/components/examples/BadRequestResponse", example="BadRequestResponse"),
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse",
     *       examples={
     *       "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse"),
     *       }
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * )
     */
    public function price(Request $request, array $suppliers): JsonResponse
    {
        try {
            $filters = $request->all();

            $keyPricingSearch = request()->get('type') . ':pricingSearch:' . http_build_query(Arr::dot($filters));

            if (Cache::has($keyPricingSearch . ':result')) {
                $res = Cache::get($keyPricingSearch . ':result');
            } else {

                if (!isset($filters['rating'])) {
                    $filters['rating'] = GeneralConfiguration::latest()->first()->star_ratings ?? 3;
                }

                $search_id = (string)Str::uuid();

                $dataResponse = [];
                $clientResponse = [];
                $countResponse = 0;
                $countClientResponse = 0;
                foreach ($suppliers as $supplier) {
                    $supplierName = Supplier::find($supplier)?->name;

                    if (isset($request->supplier) && $request->supplier != $supplierName) {
                        continue;
                    }

                    if (SupplierNameEnum::from($supplierName) === SupplierNameEnum::EXPEDIA) {

                        if (Cache::has($keyPricingSearch . ':content:' . SupplierNameEnum::EXPEDIA->value)) {
                            $expediaResponse = Cache::get($keyPricingSearch . ':content:' . SupplierNameEnum::EXPEDIA->value);
                        } else {
                            Log::info('HotelApiHandler | price | expediaResponse | start');
                            $expediaResponse = $this->expedia->price($filters);
                            Log::info('HotelApiHandler | price | expediaResponse | end');

                            Cache::put($keyPricingSearch . ':content:' . SupplierNameEnum::EXPEDIA->value, $expediaResponse, now()->addMinutes(60));
                        }

                        $dataResponse[$supplierName] = $expediaResponse;

                        Log::info('HotelApiHandler | price | ExpediaToHotelResponse | start');
                        $dtoData = $this->ExpediaHotelPricingDto->ExpediaToHotelResponse($expediaResponse, $filters, $search_id);
                        $bookingItems[$supplierName] = $dtoData['bookingItems'];
                        $clientResponse[$supplierName] = $dtoData['response'];
                        Log::info('HotelApiHandler | price | ExpediaToHotelResponse | end');

                        $countResponse += count($expediaResponse);
                        $countClientResponse += count($clientResponse[$supplierName]);
                    }

                    if (SupplierNameEnum::from($supplierName) === SupplierNameEnum::HBSI) {
                        if (Cache::has($keyPricingSearch . ':content:' . SupplierNameEnum::HBSI->value)) {
                            $hbsiResponse = Cache::get($keyPricingSearch . ':content:' . SupplierNameEnum::HBSI->value);
                        } else {
                            Log::info('HotelApiHandler | price | hbsiResponse | start');
                            $hbsiResponse = $this->hbsi->price($filters);
                            Log::info('HotelApiHandlerhbsiRe | price | hbsiResponse | end');

                            Cache::put($keyPricingSearch . ':content:' . SupplierNameEnum::HBSI->value, $hbsiResponse, now()->addMinutes(60));
                        }

                        $dataResponse[$supplierName] = $hbsiResponse['array'];
                        $dataOriginal[$supplierName] = $hbsiResponse['original'];

                        $dtoData = $this->HbsiHotelPricingDto->HbsiToHotelResponse($hbsiResponse['array'], $filters, $search_id);
                        $bookingItems[$supplierName] = $dtoData['bookingItems'];
                        $clientResponse[$supplierName] = $dtoData['response'];

                        $countResponse += count($hbsiResponse['array']);
                        $countClientResponse += count($clientResponse[$supplierName]);

                    }
                }

                // enrichment Property Weighting
                $clientResponse = $this->propsWeight->enrichmentPricing($clientResponse, 'hotel');

                $content = [
                    'count' => $countResponse,
                    'query' => $filters,
                    'results' => $dataResponse,
                ];
                $clientContent = [
                    'count' => $countClientResponse,
                    'query' => $filters,
                    'results' => $clientResponse,
                ];

                Log::info('HotelApiHandler | price | end');

                // save data to Inspector
                SaveSearchInspector::dispatch([
                    $search_id, $filters, $dataOriginal ?? [], $content, $clientContent, $suppliers, 'price', 'hotel',
                ]);

                if (!empty($bookingItems)) {
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

                Cache::put($keyPricingSearch . ':result', $res, now()->addMinutes(60));
            }

            return $this->sendResponse($res, 'success');
        } catch (Exception|NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            Log::error('HotelApiHandler ' . $e->getMessage());

            return $this->sendError(['error' => $e->getMessage()], 'failed');
        }
    }
}
