<?php

namespace Modules\API\Controllers\ApiHandlers;

use App\Jobs\SaveBookingItems;
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
	 *   @OA\RequestBody(
	 *     description="JSON object containing the details of the reservation.",
	 *     required=true,
	 *     @OA\JsonContent(    
	 *       oneOf={
	 *            @OA\Schema(ref="#/components/schemas/ContentSearchRequestDestination"),
	 *            @OA\Schema(ref="#/components/schemas/ContentSearchRequestCoordinates"),
	 *         },
	 *       examples={
	 *           "searchByDestination": @OA\Schema(ref="#/components/examples/ContentSearchRequestDestination", example="ContentSearchRequestDestination"),
	 *           "searchByCoordinates": @OA\Schema(ref="#/components/examples/ContentSearchRequestCoordinates", example="ContentSearchRequestCoordinates"),
	 *       },
	 *     ),
	 *   ),
	 *   @OA\Response(
	 *     response=200,
	 *     description="OK",
	 *     @OA\JsonContent(
	 *       ref="#/components/schemas/ContentSearchResponse",
	 *       examples={
	 *       "searchByCoordinates": @OA\Schema(ref="#/components/examples/ContentSearchResponse", example="ContentSearchResponse"),
	 *       }
	 *     )
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
				$clientResponse = [];
				$count = 0;
				foreach ($suppliers as $supplier) {
					$supplierName = Supplier::find($supplier)->name;

					if(isset($request->supplier) && $request->supplier != $supplierName) continue;

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
	 *   	description="Giata ID of the property to get details for (e.g., 98736411).",
	 *   	@OA\Schema(
	 *   	  type="integer",
	 *   	  example=98736411
	 *   	)
	 *   ), 	    
	 *   @OA\Response(
	 *     response=200,
	 *     description="OK",
	 *     @OA\JsonContent(
	 *       ref="#/components/schemas/ContentDetailResponse",
	 *       examples={
	 *       "example1": @OA\Schema(ref="#/components/examples/ContentDetailResponse", example="ContentDetailResponse"),
	 *       }
	 *     )
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
				$clientResponse = [];
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
	 *   tags={"Pricing API"},
	 *   path="/api/pricing/search",
	 *   summary="Search Price Hotels",
	 *   description="The **'/api/pricing/search'** endpoint, when used for hotel pricing, <br> is a critical part of a hotel booking API. <br> It enables users and developers to search for and obtain detailed pricing information related to hotel accommodations.",
	 *   @OA\RequestBody(
	 *     description="JSON object containing the details of the reservation.",
	 *     required=true,
	 *     @OA\JsonContent(    
	 *       ref="#/components/schemas/PricingSearchRequest", 
	 *       examples={
	 *           "NewYork": @OA\Schema(ref="#/components/examples/PricingSearchRequestNewYork", example="PricingSearchRequestNewYork"),
	 *           "London": @OA\Schema(ref="#/components/examples/PricingSearchRequestLondon", example="PricingSearchRequestLondon"),
	 *       },
	 *     ),
	 *   ),
	 *   @OA\Response(
	 *     response=200,
	 *     description="OK",
	 *     @OA\JsonContent(
	 *       ref="#/components/schemas/PricingSearchResponse", 
	 *		 examples={
	 *           "NewYork": @OA\Schema(ref="#/components/examples/PricingSearchResponseNewYork", example="PricingSearchResponseNewYork"),
	 *           "London": @OA\Schema(ref="#/components/examples/PricingSearchResponseLondon", example="PricingSearchResponseLondon"),
	 *       },
	 *     )
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

			\Log::info('ExpediaHotelApiHandler | price | start');

			$dataResponse = [];
			$clientResponse = [];
			$countResponse = 0;
			$countClientResponse = 0;
			foreach ($suppliers as $supplier) {
				$supplierName = Supplier::find($supplier)->name;

				if(isset($request->supplier) && $request->supplier != $supplierName) continue;

				if ($supplierName == self::SUPPLIER_NAME) {

					if ( Cache::has($keyPricingSearch . ':content:' . self::SUPPLIER_NAME) ) {
						$expediaResponse = Cache::get($keyPricingSearch . ':content:' . self::SUPPLIER_NAME);
					} else {

						\Log::info('ExpediaHotelApiHandler | price | expediaResponse | start');
						$expediaResponse = $this->expedia->price($request, $filters);
						\Log::info('ExpediaHotelApiHandler | price | expediaResponse | end');

						Cache::put($keyPricingSearch . ':content:' . self::SUPPLIER_NAME, $expediaResponse, now()->addMinutes(60));
					}

					$dataResponse[$supplierName] = $expediaResponse;

					\Log::info('ExpediaHotelApiHandler | price | ExpediaToHotelResponse | start');
					$dtoData = $this->expediaPricingDto->ExpediaToHotelResponse($expediaResponse, $filters, $search_id);
					$bookingItems = $dtoData['bookingItems'];
					$clientResponse[$supplierName] = $dtoData['response'];
					\Log::info('ExpediaHotelApiHandler | price | ExpediaToHotelResponse | end');

					$countResponse += count($expediaResponse);
					$countClientResponse += count($clientResponse[$supplierName]);
				}
				// TODO: Add other suppliers
			}

			# enrichment Property Weighting
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

			\Log::info('ExpediaHotelApiHandler | price | end');

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

			if (isset($bookingItems)) SaveBookingItems::dispatch($bookingItems);

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
