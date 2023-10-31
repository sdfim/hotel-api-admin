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

				$dataResponse = [];
				$clientResponse = [];
				foreach ($suppliers as $supplier) {
					$supplierName = Supplier::find($supplier)->name;
					if ($supplierName == self::SUPPLIER_NAME) {
						$expediaResponse = $this->expedia->price($request, $filters);
						$dataResponse[$supplierName] = $expediaResponse;
						$clientResponse[$supplierName] = $this->expediaPricingDto->ExpediaToHotelResponse($expediaResponse, $filters, $search_id);
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
