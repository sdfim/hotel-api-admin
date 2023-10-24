<?php

namespace Modules\API\Controllers\ApiHandlers;

use App\Jobs\SaveSearchInspector;
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
    private const SUPPLIER_NAME = 'Expedia';
    private ExpediaService $expediaService;
    private SearchInspectorController $apiInspector;
    private ExpediaHotelApiHandler $expedia;
    private ExpediaPricingDto $expediaPricingDto;
    private ExpediaContentDto $expediaContentDto;
    private ExpediaContentDetailDto $expediaContentDetailDto;
    private EnrichmentWeight $propsWeight;

    public function __construct(ExpediaService $expediaService) {
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
    public function search(Request $request, array $suppliers) : JsonResponse
    {
        try {
            $searchRequest = new SearchHotelRequest();
            $rules = $searchRequest->rules();
            $filters = Validator::make($request->all(), $rules)->validated();

            $dataResponse = [];
            $count = 0;
            foreach ($suppliers as $supplier) {
                $supplierName = Supplier::find($supplier)->name;
                if ($supplierName == self::SUPPLIER_NAME) {
                    $supplierData = $this->expedia->search($request, $filters);
                    $data = $supplierData['results'];
                    $count += $supplierData['count'];
                    $dataResponse[$supplierName] =  $data;
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

            if ($request->input('supplier_data') == 'true') $res = $content;
            else $res = $clientContent;

            return $this->sendResponse($res, 'success');

        } catch (\Exception $e) {
            \Log::error('ExpediaHotelApiHandler | search' . $e->getMessage());
            return $this->sendError(['error' => $e->getMessage()], 'falied');
        }

    }

    /*
     * @param Request $request
     * @return JsonResponse
     */
    public function detail(Request $request, array $supplierIds) : JsonResponse
    {
        try {
            // $detailRequest = new DetailHotelRequest();
            // $rules = $detailRequest->rules();
            // $validator = Validator::make($request->all(), $rules)->validated();

            $dataResponse = [];
            foreach ($supplierIds as $supplier) {
                $supplierName = Supplier::find($supplier)->name;
                if ($supplierName == self::SUPPLIER_NAME) {
                    $data = $this->expedia->detail($request);
                    $dataResponse[$supplierName] = $data;
                    $clientResponse[$supplierName] = $this->expediaContentDetailDto->ExpediaToContentDetailResponse($data->first(), $request->input('property_id'));
                }
                // TODO: Add other suppliers
            }

            if ($request->input('supplier_data') == 'true') $results = $dataResponse;
            else $results = $clientResponse;

            return $this->sendResponse(['results' => $results], 'success');
        } catch (\Exception $e) {
            \Log::error('ExpediaHotelApiHandler ' . $e->getMessage());
            return $this->sendError(['error' => $e->getMessage()], 'falied');
        }

    }

    /*
     * @param Request $request
     * @return JsonResponse
     */
    public function price(Request $request, array $supplierIds) : JsonResponse
    {
        try{
            $priceRequest = new PriceHotelRequest();
            $rules = $priceRequest->rules();
            $filters = Validator::make($request->all(), $rules)->validated();

            $search_id = (string) Str::uuid();

            $dataResponse = [];
            $clientResponse = [];
            foreach ($supplierIds as $supplier) {
                $supplierName = Supplier::find($supplier)->name;
                if ($supplierName == self::SUPPLIER_NAME) {
                    $expediaResponse = $this->expedia->price($request, $filters);
                    $dataResponse[$supplierName] = $expediaResponse;
                    $clientResponse[$supplierName] = $this->expediaPricingDto->ExpediaToHotelResponse((array)$expediaResponse, $filters, $search_id);
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

            # save data to Inspector
            SaveSearchInspector::dispatch([
                $search_id,
                $filters,
                $content,
                $clientContent,
                $supplierIds,
                'search',
                'hotel'
            ]);

            if ($request->input('supplier_data') == 'true') $res = $content;
            else $res = $clientContent;

            $res['search_id'] = $search_id;

            return $this->sendResponse($res, 'success');
        } catch (\Exception $e) {
            \Log::error('ExpediaHotelApiHandler ' . $e->getMessage());
            return $this->sendError(['error' => $e->getMessage()], 'falied');
        }

    }
}
