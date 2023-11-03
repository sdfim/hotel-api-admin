<?php

namespace Modules\API\Controllers\ApiHandlers;

use App\Jobs\SaveSearchInspector;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Modules\API\Controllers\ApiHandlerInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\API\BaseController;
use App\Models\Supplier;
use Modules\API\Controllers\ExpediaHotelApiHandler;
use Modules\API\Requests\SearchHotelRequest;
use Illuminate\Support\Facades\Validator;
use Modules\API\Suppliers\ExpediaSupplier\ExpediaService;
use Modules\Inspector\SearchInspectorController;
use Modules\API\Requests\PriceHotelRequest;
use Modules\API\Suppliers\DTO\ExpediaPricingDto;
use Modules\API\Suppliers\DTO\ExpediaContentDto;
use Modules\API\Suppliers\DTO\ExpediaContentDetailDto;
use Illuminate\Support\Str;
use Modules\API\PropertyWeighting\EnrichmentWeight;
use OpenApi\Annotations as OA;

/**
 * @OA\PathItem(
 * path="/api/content",
 * )
 */

class HotelApiHanlder extends BaseController implements ApiHandlerInterface
{
	/**
	 *
	 */
	private const SUPPLIER_NAME = 'Expedia';
	/**
	 * @var ExpediaService
	 */
	private ExpediaService $expediaService;
	/**
	 * @var SearchInspectorController
	 */
	private SearchInspectorController $apiInspector;
	/**
	 * @var ExpediaHotelApiHandler
	 */
	private ExpediaHotelApiHandler $expedia;
	/**
	 * @var ExpediaPricingDto
	 */
	private ExpediaPricingDto $expediaPricingDto;
	/**
	 * @var ExpediaContentDto
	 */
	private ExpediaContentDto $expediaContentDto;
	/**
	 * @var ExpediaContentDetailDto
	 */
	private ExpediaContentDetailDto $expediaContentDetailDto;
	/**
	 * @var EnrichmentWeight
	 */
	private EnrichmentWeight $propsWeight;

	/**
	 * @param ExpediaService $expediaService
	 */
	public function __construct(ExpediaService $expediaService)
	{
		$this->expediaService = $expediaService;
		$this->expedia = new ExpediaHotelApiHandler($this->expediaService);
		$this->apiInspector = new SearchInspectorController();
		$this->expediaPricingDto = new ExpediaPricingDto();
		$this->expediaContentDto = new ExpediaContentDto();
		$this->expediaContentDetailDto = new ExpediaContentDetailDto();
		$this->propsWeight = new EnrichmentWeight();
	}
	/*
     * @param Request $request
     * @return JsonResponse
     */
	/**
	 * @param Request $request
	 * @param array $suppliers
	 * @return JsonResponse
	 */
	/**
	 * @OA\Post(
	 *   tags={"Content API"},
	 *   path="/api/content/search",
	 *   summary="Search Hotels",
	 *   description="Search for hotels by destination or coordinates.",
	 *    @OA\Parameter(
	 *      name="type",
	 *      in="query",
	 *      required=true,
	 *      description="Type of content to search (e.g., 'hotel').",
	 *      @OA\Schema(
	 *        type="string",
	 *        example="hotel"
	 *        )
	 *    ),
	 *    @OA\Parameter(
	 *      name="destination",
	 *      in="query",
	 *      required=true,
	 *      description="ID of the destination for the content search.",
	 *    	@OA\Schema(
	 *    	  type="integer",
	 *    	  example=961
	 *      )
	 *    ),
	 *    @OA\Parameter(
	 *      name="rating",
	 *      in="query",
	 *      required=true,
	 *      description="Minimum rating of the content (e.g., 4).",
	 *      @OA\Schema(
	 *        type="number",
	 *        example=4
	 *      )
	 *    ),
	 *    @OA\Parameter(
	 *      name="page",
	 *      in="query",
	 *      description="Page number for pagination (e.g., 1).",
	 *      @OA\Schema(
	 *        type="integer",
	 *        example=1
	 *      )
	 *    ),
	 *    @OA\Parameter(
	 *      name="results_per_page",
	 *      in="query",
	 *      description="Number of results to return per page (e.g., 250).",
	 *      @OA\Schema(
	 *        type="integer",
	 *        example=250
	 *      )
	 *    ),
	 *   @OA\RequestBody(
	 *     @OA\MediaType(
	 *       mediaType="application/json",
	 *       @OA\Schema(
	 *         @OA\Property(
	 *           property="type",
	 *           type="string",
	 *           description="Type of content to search (e.g., 'hotel')."
	 *         ),
	 *         @OA\Property(
	 *           property="destination",
	 *           type="integer",
	 *           description="ID of the destination for the content search."
	 *         ),
	 *         @OA\Property(
	 *           property="rating",
	 *           type="number",
	 *           description="Minimum rating of the content (e.g., 4)."
	 *         ),
	 *         @OA\Property(
	 *           property="page",
	 *           type="integer",
	 *           description="Page number for pagination (e.g., 1)."
	 *         ),
	 *         @OA\Property(
	 *           property="results_per_page",
	 *           type="integer",
	 *           description="Number of results to return per page (e.g., 250)."
	 *         ),
	 *         example={
	 *           "type": "hotel",
	 *           "destination": 961,
	 *           "rating": 4,
	 *           "page": 1,
	 *           "results_per_page": 250
	 *         },
	 *         @OA\Examples(example="withCity", summary="With City", value={
	 *           "type": "restaurant",
	 *           "destination": 123,
	 *           "rating": 3.5,
	 *           "page": 2,
	 *           "results_per_page": 10
	 *         }),
	 *         @OA\Examples(example="withCoordinates", summary="With Coordinates",value={
	 *           "type": "attraction",
	 *           "destination": 789,
	 *           "rating": 4.5,
	 *           "page": 3,
	 *           "results_per_page": 20
	 *         })
	 *       )
	 *     ),
	 *   ),
	 *   @OA\Response(
	 *     response=200,
	 *     description="OK",
	 *   ),
	 *   @OA\Response(
	 *       response=401,
	 *       description="Unauthenticated",
	 *   ),
	 *   @OA\Response(
	 *       response=403,
	 *       description="Forbidden"
	 *   ),
	 *   security={{ "apiAuth": {} }}
	 * )
	 */
	public function search(Request $request, array $suppliers): JsonResponse
	{
		try {
			$searchRequest = new SearchHotelRequest();
			$rules = $searchRequest->rules();
			$filters = Validator::make($request->all(), $rules)->validated();

			$keyPricingSearch = request()->get('type') . ':contentSearch:' . http_build_query(Arr::dot($filters));

			if (Cache::has($keyPricingSearch . ':content') && Cache::has($keyPricingSearch . ':clientContent')) {

				$content = Cache::get($keyPricingSearch . ':content');
				$clientContent = Cache::get($keyPricingSearch . ':clientContent');
			} else {

				$dataResponse = [];
				$count = 0;
				foreach ($suppliers as $supplier) {
					$supplierName = Supplier::find($supplier)->name;
					if ($supplierName == self::SUPPLIER_NAME) {
						$supplierData = $this->expedia->search($request, $filters);
						$data = $supplierData['results'];
						$count += $supplierData['count'];
						$dataResponse[$supplierName] = $data;
						$clientResponse[$supplierName] = $this->expediaContentDto->ExpediaToContentSearchResponse($data);
					}
					// TODO: Add other suppliers
				}

				# enrichment Property Weighting
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

			return $this->sendResponse($res, 'success');
		} catch (Exception $e) {
			\Log::error('ExpediaHotelApiHandler | search' . $e->getMessage());
			return $this->sendError(['error' => $e->getMessage()], 'failed');
		}
	}

	/*
     * @param Request $request
     * @return JsonResponse
     */
	/**
	 * @param Request $request
	 * @param array $suppliers
	 * @return JsonResponse
	 */
	/**
	 * @OA\Get(
	 *   tags={"Content API"},
	 *   path="/api/content/detail",
	 *   summary="Delail Hotels",
	 *   description="Get detailed information about a hotel.",
	 *    @OA\Parameter(
	 *      name="type",
	 *      in="query",
	 *      required=true,
	 *      description="Type of content to search (e.g., 'hotel').",
	 *      @OA\Schema(
	 *        type="string",
	 *        example="hotel"
	 *        )
	 *    ),
	 *    @OA\Parameter(
	 *      name="property_id",
	 *   	in="query",
	 *   	required=true,
	 *   	description="ID of the property to get details for (e.g., 98736411).",
	 *   	@OA\Schema(
	 *   	  type="integer",
	 *   	  example=98736411
	 *   	)
	 *   ), 	    
	 *   @OA\Response(
	 *     response=200,
	 *     description="OK",
	 *   ),
	 *   @OA\Response(
	 *       response=401,
	 *       description="Unauthenticated",
	 *   ),
	 *   @OA\Response(
	 *       response=403,
	 *       description="Forbidden"
	 *   ),
	 *   security={{ "apiAuth": {} }}
	 * )
	 */
	public function detail(Request $request, array $suppliers): JsonResponse
	{
		try {
			// $detailRequest = new DetailHotelRequest();
			// $rules = $detailRequest->rules();
			// $validator = Validator::make($request->all(), $rules)->validated();

			$keyPricingSearch = request()->get('type') . ':contentDetail:' . http_build_query(Arr::dot($request->all()));

			if (Cache::has($keyPricingSearch . ':dataResponse') && Cache::has($keyPricingSearch . ':clientResponse')) {

				$dataResponse = Cache::get($keyPricingSearch . ':dataResponse');
				$clientResponse = Cache::get($keyPricingSearch . ':clientResponse');
			} else {

				$dataResponse = [];
				foreach ($suppliers as $supplier) {
					$supplierName = Supplier::find($supplier)->name;
					if ($supplierName == self::SUPPLIER_NAME) {
						$data = $this->expedia->detail($request);
						$dataResponse[$supplierName] = $data;
						$clientResponse[$supplierName] = $this->expediaContentDetailDto->ExpediaToContentDetailResponse($data->first(), $request->input('property_id'));
					}
					// TODO: Add other suppliers
				}

				Cache::put($keyPricingSearch . ':dataResponse', $dataResponse, now()->addMinutes(60));
				Cache::put($keyPricingSearch . ':clientResponse', $clientResponse, now()->addMinutes(60));
			}

			if ($request->input('supplier_data') == 'true') $results = $dataResponse;
			else $results = $clientResponse;

			return $this->sendResponse(['results' => $results], 'success');
		} catch (Exception $e) {
			\Log::error('ExpediaHotelApiHandler ' . $e->getMessage());
			return $this->sendError(['error' => $e->getMessage()], 'failed');
		}
	}

	/*
     * @param Request $request
     * @return JsonResponse
     */
	/**
	 * @param Request $request
	 * @param array $suppliers
	 * @return JsonResponse
	 */
	/**
	 * @OA\Post(
	 *   tags={"Price API"},
	 *   path="/api/pricing/search",
	 *   summary="Search Price Hotels",
	 *   description="Endpoint for making a hotel reservation.",
	 *   @OA\Parameter(
	 *     name="type",
	 *     in="query",
	 *     required=true,
	 *     description="Type of reservation (e.g., 'hotel').",
	 *     @OA\Schema(
	 *       type="string",
	 *       example="hotel"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="checkin",
	 *     in="query",
	 *     required=true,
	 *     description="Check-in date in YYYY-MM-DD format (e.g., '2023-11-11').",
	 *     @OA\Schema(
	 *       type="date",
	 *       example="2023-11-11"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="checkout",
	 *     in="query",
	 *     required=true,
	 *     description="Check-out date in YYYY-MM-DD format (e.g., '2023-11-21').",
	 *     @OA\Schema(
	 *       type="date",
	 *       example="2023-11-21"
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="destination",
	 *     in="query",
	 *     required=true,
	 *     description="ID of the destination for the reservation (e.g., 1175).",
	 *     @OA\Schema(
	 *       type="integer",
	 *       example=1175
	 *    )
	 *   ),
	 *   @OA\Parameter(
	 *     name="rating",
	 *     in="query",
	 *     required=true,
	 *     description="Rating of the hotel (e.g., 3.5).",
	 *     @OA\Schema(
	 *       type="number",
	 *       example=3.5
	 *     )
	 *   ),
	 *   @OA\Parameter(
	 *     name="occupancy",
	 *     in="query",
	 *     required=true,
	 *     description="Array of occupancy details. For each guest, specify the number of adults and children.",
	 *     @OA\Schema(
	 *       type="array",
	 *       @OA\Items(
	 *         type="object",
	 *         @OA\Property(
	 *           property="adults",
	 *           type="integer",
	 *           description="Number of adults in the room."
	 *         ),
	 *         @OA\Property(
	 *           property="children",
	 *           type="integer",
	 *           description="Number of children in the room."
	 *         )
	 *       )
	 *     ),
	 *     example={
	 *     {
	 *     "adults": 2,
	 *     "children": 1
	 *     },
	 *     {
	 *     "adults": 1
	 *     }
	 *     }
	 *   ),
	 *   @OA\RequestBody(
	 *     description="JSON object containing the details of the reservation.",
	 *     required=true,
	 *     @OA\MediaType(
	 *       mediaType="application/json",
	 *       @OA\Schema(
	 *         @OA\Property(
	 *           property="type",
	 *           type="string",
	 *           description="Type of reservation (e.g., 'hotel')."
	 *         ),
	 *         @OA\Property(
	 *           property="checkin",
	 *           type="date",
	 *           description="Check-in date in YYYY-MM-DD format (e.g., '2023-11-11')."
	 *         ),
	 *         @OA\Property(
	 *           property="checkout",
	 *           type="date",
	 *           description="Check-out date in YYYY-MM-DD format (e.g., '2023-11-21')."
	 *         ),
	 *         @OA\Property(
	 *           property="destination",
	 *           type="integer",
	 *           description="ID of the destination for the reservation (e.g., 1175)."
	 *         ),
	 *         @OA\Property(
	 *           property="rating",
	 *           type="number",
	 *           description="Rating of the hotel (e.g., 3.5)."
	 *         ),
	 *         @OA\Property(
	 *           property="occupancy",
	 *           type="array",
	 *           description="Array of occupancy details. For each guest, specify the number of adults and children.",
	 *           @OA\Items(
	 *             type="object",
	 *             @OA\Property(
	 *               property="adults",
	 *               type="integer",
	 *               description="Number of adults in the room."
	 *             ),
	 *             @OA\Property(
	 *               property="children",
	 *               type="integer",
	 *               description="Number of children in the room."
	 *             )
	 *           )
	 *         )
	 *       ),
	 *       example={
	 *         "type": "hotel",
	 *         "checkin": "2023-11-11",
	 *         "checkout": "2023-11-21",
	 *         "destination": 1175,
	 *         "rating": 3.5,
	 *         "occupancy": {
	 *           {
	 *             "adults": 2,
	 *             "children": 1
	 *           },
	 *           {
	 *             "adults": 1
	 *           }
	 *         }
	 *       }
	 *     ),
	 *   ),
	 *   @OA\Response(
	 *     response=200,
	 *     description="OK",
	 *   ),
	 *   security={{ "apiAuth": {} }}
	 * )
	 */

	public function price(Request $request, array $suppliers): JsonResponse
	{
		try {
			$priceRequest = new PriceHotelRequest();
			$rules = $priceRequest->rules();
			$filters = Validator::make($request->all(), $rules)->validated();

			$search_id = (string)Str::uuid();

			$keyPricingSearch = request()->get('type') . ':pricingSearch:' . http_build_query(Arr::dot($filters));

			if (Cache::has($keyPricingSearch . ':content') && Cache::has($keyPricingSearch . ':clientContent')) {

				$content = Cache::get($keyPricingSearch . ':content');
				$clientContent = Cache::get($keyPricingSearch . ':clientContent');
			} else {

				\Log::info('ExpediaHotelApiHandler | price | start');

				$dataResponse = [];
				$clientResponse = [];
				foreach ($suppliers as $supplier) {
					$supplierName = Supplier::find($supplier)->name;
					if ($supplierName == self::SUPPLIER_NAME) {

						\Log::info('ExpediaHotelApiHandler | price | expediaResponse | start');
						$expediaResponse = $this->expedia->price($request, $filters);
						\Log::info('ExpediaHotelApiHandler | price | expediaResponse | end');

						$dataResponse[$supplierName] = $expediaResponse;
						\Log::info('ExpediaHotelApiHandler | price | ExpediaToHotelResponse | start');
						$clientResponse[$supplierName] = $this->expediaPricingDto->ExpediaToHotelResponse($expediaResponse, $filters, $search_id);
						\Log::info('ExpediaHotelApiHandler | price | ExpediaToHotelResponse | end');
					}
					// TODO: Add other suppliers
				}

				# enrichment Property Weighting
				$clientResponse = $this->propsWeight->enrichmentPricing($clientResponse, 'hotel');

				$content = [
					'count' => count($dataResponse[self::SUPPLIER_NAME]),
					'query' => $filters,
					'results' => $dataResponse,
				];
				$clientContent = [
					'count' => count($clientResponse[self::SUPPLIER_NAME]),
					'query' => $filters,
					'results' => $clientResponse,
				];

				Cache::put($keyPricingSearch . ':content', $content, now()->addMinutes(60));
				Cache::put($keyPricingSearch . ':clientContent', $clientContent, now()->addMinutes(60));

				\Log::info('ExpediaHotelApiHandler | price | end');
			}

			# save data to Inspector
			SaveSearchInspector::dispatch([
				$search_id,
				$filters,
				$content,
				$clientContent,
				$suppliers,
				'search',
				'hotel'
			]);

			if ($request->input('supplier_data') == 'true') $res = $content;
			else $res = $clientContent;

			$res['search_id'] = $search_id;

			return $this->sendResponse($res, 'success');
		} catch (Exception $e) {
			\Log::error('ExpediaHotelApiHandler ' . $e->getMessage());
			return $this->sendError(['error' => $e->getMessage()], 'failed');
		}
	}
}
