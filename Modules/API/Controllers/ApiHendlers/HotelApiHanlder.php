<?php

namespace Modules\API\Controllers\ApiHendlers;

use Modules\API\Controllers\ApiHandlerInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\API\BaseController;
use App\Models\Suppliers;
use Modules\API\Controllers\ExpediaHotelApiHandler;
use Modules\API\Requests\SearchHotelRequest;
use Illuminate\Support\Facades\Validator;
use Modules\API\Suppliers\ExpediaSupplier\ExperiaService;
use Modules\Inspector\SearchInspectorController;
use Modules\API\Requests\PriceHotelRequest;
use Modules\API\Suppliers\DTO\ExpediaHotelDto;

class HotelApiHanlder extends BaseController implements ApiHandlerInterface
{
	private const SUPPLIER_NAME = 'Expedia';
	private ExperiaService $experiaService;
	private SearchInspectorController $apiInspector;
	private ExpediaHotelApiHandler $expedia;
	private ExpediaHotelDto $expediaDto;

	public function __construct(ExperiaService $experiaService) {
		$this->experiaService = $experiaService;
		$this->expedia = new ExpediaHotelApiHandler($this->experiaService);
		$this->apiInspector = new SearchInspectorController();
		$this->expediaDto = new ExpediaHotelDto();
	}
	/*
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function search(Request $request, array $supplierIds) : JsonResponse
	{	
		try {
			$searchRequest = new SearchHotelRequest();
			$rules = $searchRequest->rules();
			$filters = Validator::make($request->all(), $rules)->validated();
			
			$daraResponse = [];
			$count = 0;
			foreach ($supplierIds as $supplier) {
				$supplierName = Suppliers::find($supplier)->name;
				if ($supplierName == self::SUPPLIER_NAME) {
					$data = $this->expedia->search($request, $filters);
					$daraResponse[$supplierName] =  $data;
					$count += count($data);
				}
				// TODO: Add other suppliers
			}

			return $this->sendResponse(['count' => $count, 'query' => $filters,  'results' => $daraResponse], 'success');
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

			$daraResponse = [];
			foreach ($supplierIds as $supplier) {
				$supplierName = Suppliers::find($supplier)->name;
				if ($supplierName == self::SUPPLIER_NAME) {
					$daraResponse[$supplierName] = $this->expedia->detail($request);
				}
				// TODO: Add other suppliers
			}

            return $this->sendResponse(['results' => $daraResponse], 'success');
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

			$dataResponse = [];
			$clientResponse = [];
			foreach ($supplierIds as $supplier) {
				$supplierName = Suppliers::find($supplier)->name;
				if ($supplierName == self::SUPPLIER_NAME) {
					$expediaResponse = $this->expedia->price($request, $filters);
					$dataResponse[$supplierName] = $expediaResponse;
					$clientResponse[$supplierName] = $this->expediaDto->ExpediaToHotelResponse($expediaResponse, $filters);
				}
				// TODO: Add other suppliers
			}

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
			$search_id = $this->apiInspector->save($filters, $content, $clientContent, $supplierIds, 'search', 'hotel');

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