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


class HotelApiHenlder extends BaseController implements ApiHandlerInterface
{
	private const SUPPLIER_NAME = 'Expedia';
	private ExperiaService $experiaService;
	private SearchInspectorController $apiInspector;
	private ExpediaHotelApiHandler $expedia;

	public function __construct(ExperiaService $experiaService) {
		$this->experiaService = $experiaService;
		$this->expedia = new ExpediaHotelApiHandler($this->experiaService);
		$this->apiInspector = new SearchInspectorController();
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

			$daraResponse = [];
			foreach ($supplierIds as $supplier) {
				$supplierName = Suppliers::find($supplier)->name;
				if ($supplierName == self::SUPPLIER_NAME) {
					$daraResponse[$supplierName] = $this->expedia->price($request, $filters);
				}
				// TODO: Add other suppliers
			}
			$res = [
				'count' => count($daraResponse[self::SUPPLIER_NAME]), 
				'query' => $filters, 
				'results' => $daraResponse,
				];

			# save data to Inspector
			$inspector = $this->apiInspector->save($filters, $res, $supplierIds);

			$res['inspector'] = $inspector;

			return $this->sendResponse($res, 'success');
		} catch (\Exception $e) {
			\Log::error('ExpediaHotelApiHandler ' . $e->getMessage());
			return $this->sendError(['error' => $e->getMessage()], 'falied');
		}

	}

	private function executionTime ()
	{
		return microtime(true) - $this->current_time;
	}
}