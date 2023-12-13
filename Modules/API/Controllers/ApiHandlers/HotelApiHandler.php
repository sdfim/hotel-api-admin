<?php

namespace Modules\API\Controllers\ApiHandlers;

use App\Jobs\SaveBookingItems;
use App\Jobs\SaveSearchInspector;
use App\Models\GeneralConfiguration;
use App\Models\Supplier;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\API\BaseController;
use Modules\API\Controllers\ApiHandlerInterface;
use Modules\API\Controllers\ExpediaHotelApiHandler;
use Modules\API\PropertyWeighting\EnrichmentWeight;
use Modules\API\Requests\PriceHotelRequest;
use Modules\API\Requests\SearchHotelRequest;
use Modules\API\Suppliers\DTO\ExpediaHotelContentDetailDto;
use Modules\API\Suppliers\DTO\ExpediaHotelContentDto;
use Modules\API\Suppliers\DTO\ExpediaHotelPricingDto;
use Modules\Inspector\SearchInspectorController;
use OpenApi\Annotations as OA;

/**
 * @OA\PathItem(
 * path="/api/content",
 * )
 */
class HotelApiHandler extends BaseController implements ApiHandlerInterface
{
    private const SUPPLIER_NAME = 'Expedia';

    private SearchInspectorController $apiInspector;

    private ExpediaHotelApiHandler $expedia;

    private ExpediaHotelPricingDto $ExpediaHotelPricingDto;

    private ExpediaHotelContentDto $ExpediaHotelContentDto;

    private ExpediaHotelContentDetailDto $ExpediaHotelContentDetailDto;

    private EnrichmentWeight $propsWeight;

    public function __construct()
    {
        $this->expedia = new ExpediaHotelApiHandler();
        $this->apiInspector = new SearchInspectorController();
        $this->ExpediaHotelPricingDto = new ExpediaHotelPricingDto();
        $this->ExpediaHotelContentDto = new ExpediaHotelContentDto();
        $this->ExpediaHotelContentDetailDto = new ExpediaHotelContentDetailDto();
        $this->propsWeight = new EnrichmentWeight();
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
    public function search(Request $request, array $suppliers): JsonResponse
    {
        try {
            $validate = Validator::make($request->all(), (new SearchHotelRequest())->rules());
            if ($validate->fails()) {
                return $this->sendError($validate->errors());
            }

            $filters = $request->all();

            $keyPricingSearch = request()->get('type').':contentSearch:'.http_build_query(Arr::dot($filters));

            if (Cache::has($keyPricingSearch.':content') && Cache::has($keyPricingSearch.':clientContent')) {

                $content = Cache::get($keyPricingSearch.':content');
                $clientContent = Cache::get($keyPricingSearch.':clientContent');
            } else {

                $dataResponse = [];
                $clientResponse = [];
                $count = 0;
                foreach ($suppliers as $supplier) {
                    $supplierName = Supplier::find($supplier)->name;

                    if (isset($request->supplier) && $request->supplier != $supplierName) {
                        continue;
                    }

                    if ($supplierName == self::SUPPLIER_NAME) {
                        $supplierData = $this->expedia->search($filters);
                        $data = $supplierData['results'];
                        $count += $supplierData['count'];
                        $dataResponse[$supplierName] = $data;
                        $clientResponse[$supplierName] = $this->ExpediaHotelContentDto->ExpediaToContentSearchResponse($data);
                    }
                    // TODO: Add other suppliers
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

                Cache::put($keyPricingSearch.':content', $content, now()->addMinutes(60));
                Cache::put($keyPricingSearch.':clientContent', $clientContent, now()->addMinutes(60));
            }

            if ($request->input('supplier_data') == 'true') {
                $res = $content;
            } else {
                $res = $clientContent;
            }

            return $this->sendResponse($res, 'success');
        } catch (Exception $e) {
            \Log::error('HotelApiHandler | search'.$e->getMessage());

            return $this->sendError(['error' => $e->getMessage()], 'failed');
        }
    }

    /*
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
     *   	in="query",
     *   	required=true,
     *   	description="Giata ID of the property to get details for (e.g., 98736411).",
     *
     *   	@OA\Schema(
     *   	  type="integer",
     *   	  example=98736411
     *   	)
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
    public function detail(Request $request, array $suppliers): JsonResponse
    {
        try {
            $validate = Validator::make($request->all(), [
                'property_id' => 'required|string',
                'type' => 'required|in:hotel,flight,combo',
            ]);
            if ($validate->fails()) {
                return $this->sendError($validate->errors());
            }

            $keyPricingSearch = request()->get('type').':contentDetail:'.http_build_query(Arr::dot($request->all()));

            if (Cache::has($keyPricingSearch.':dataResponse') && Cache::has($keyPricingSearch.':clientResponse')) {

                $dataResponse = Cache::get($keyPricingSearch.':dataResponse');
                $clientResponse = Cache::get($keyPricingSearch.':clientResponse');
            } else {

                $dataResponse = [];
                $clientResponse = [];
                foreach ($suppliers as $supplier) {
                    $supplierName = Supplier::find($supplier)->name;
                    if ($supplierName == self::SUPPLIER_NAME) {
                        $data = $this->expedia->detail($request);
                        $dataResponse[$supplierName] = $data;
                        $clientResponse[$supplierName] = $this->ExpediaHotelContentDetailDto->ExpediaToContentDetailResponse($data->first(), $request->input('property_id'));
                    }
                    // TODO: Add other suppliers
                }

                Cache::put($keyPricingSearch.':dataResponse', $dataResponse, now()->addMinutes(60));
                Cache::put($keyPricingSearch.':clientResponse', $clientResponse, now()->addMinutes(60));
            }

            if ($request->input('supplier_data') == 'true') {
                $results = $dataResponse;
            } else {
                $results = $clientResponse;
            }

            return $this->sendResponse(['results' => $results], 'success');
        } catch (Exception $e) {
            \Log::error('HotelApiHandler '.$e->getMessage());

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
     *		 examples={
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
            $validate = Validator::make($request->all(), (new PriceHotelRequest())->rules());
            if ($validate->fails()) {
                return $this->sendError($validate->errors());
            }

            $filters = $request->all();
            if (! isset($filters['rating'])) {
                $filters['rating'] = GeneralConfiguration::latest()->first()->star_ratings ?? 3;
            }

            $search_id = (string) Str::uuid();

            $keyPricingSearch = request()->get('type').':pricingSearch:'.http_build_query(Arr::dot($filters));

            $dataResponse = [];
            $clientResponse = [];
            $countResponse = 0;
            $countClientResponse = 0;
            foreach ($suppliers as $supplier) {
                $supplierName = Supplier::find($supplier)->name;

                if (isset($request->supplier) && $request->supplier != $supplierName) {
                    continue;
                }

                if ($supplierName == self::SUPPLIER_NAME) {

                    if (Cache::has($keyPricingSearch.':content:'.self::SUPPLIER_NAME)) {
                        $expediaResponse = Cache::get($keyPricingSearch.':content:'.self::SUPPLIER_NAME);
                    } else {

                        \Log::info('HotelApiHandler | price | expediaResponse | start');
                        $expediaResponse = $this->expedia->price($filters);
                        \Log::info('HotelApiHandler | price | expediaResponse | end');

                        Cache::put($keyPricingSearch.':content:'.self::SUPPLIER_NAME, $expediaResponse, now()->addMinutes(60));
                    }

                    $dataResponse[$supplierName] = $expediaResponse;

                    \Log::info('HotelApiHandler | price | ExpediaToHotelResponse | start');
                    $dtoData = $this->ExpediaHotelPricingDto->ExpediaToHotelResponse($expediaResponse, $filters, $search_id);
                    $bookingItems = $dtoData['bookingItems'];
                    $clientResponse[$supplierName] = $dtoData['response'];
                    \Log::info('HotelApiHandler | price | ExpediaToHotelResponse | end');

                    $countResponse += count($expediaResponse);
                    $countClientResponse += count($clientResponse[$supplierName]);
                }
                // TODO: Add other suppliers
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

            \Log::info('HotelApiHandler | price | end');

            // save data to Inspector
            SaveSearchInspector::dispatch([
                $search_id,
                $filters,
                $content,
                $clientContent,
                $suppliers,
                'search',
                'hotel',
            ]);

            if (isset($bookingItems)) {
                SaveBookingItems::dispatch($bookingItems);
            }

            if ($request->input('supplier_data') == 'true') {
                $res = $content;
            } else {
                $res = $clientContent;
            }

            $res['search_id'] = $search_id;

            return $this->sendResponse($res, 'success');
        } catch (Exception $e) {
            \Log::error('HotelApiHandler '.$e->getMessage());

            return $this->sendError(['error' => $e->getMessage()], 'failed');
        }
    }
}
