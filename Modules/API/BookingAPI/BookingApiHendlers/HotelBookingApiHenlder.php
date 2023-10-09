<?php

namespace Modules\API\BookingAPI\BookingApiHendlers;

use Modules\API\BaseController;
use App\Models\ExpediaContent;
use App\Models\MapperExpediaGiata;
use App\Models\Suppliers;
use Modules\API\BookingApi\BookingApiHandlerInterface;
use Modules\API\Requests\SearchHotelRequest;
use Modules\API\Requests\DetailHotelRequest;
use Modules\API\Requests\PriceHotelRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\API\ContentAPI\Controllers\HotelSearchBuilder;
use Modules\API\Suppliers\ExpediaSupplier\ExperiaService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Modules\Inspector\InspectorController;
use Modules\API\BookingAPI\ExpediaHotelBookingApiHandler;

class HotelBookingApiHenlder extends BaseController // implements BookingApiHandlerInterface
{
	private $experiaService;
	private $apiInspector;
	private $expedia;
	private const EXPEDIA_SUPPLIER_NAME = 'Expedia';
	public function __construct(ExperiaService $experiaService) {
		$this->experiaService = $experiaService;
		$this->apiInspector = new InspectorController();
		$this->expedia = new ExpediaHotelBookingApiHandler($this->experiaService);

	}
	/**
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function addItem (Request $request, string $supplier) : JsonResponse
	{
		try {	
			// TODO: add validation for request
			$filters = $request->all();

			$data = [];
			$count = 0;

			if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {
				$data = $this->expedia->addItem($request, $filters);
				$count += count($data);
			}
			// TODO: Add other suppliers

		} catch (\Exception $e) {
			\Log::error('ExpediaHotelApiHandler | search' . $e->getMessage());
			return $this->sendError(['error' => $e->getMessage()], 'falied');
		}

		return $this->sendResponse(['result' => $data], 'success');
	}

	/**
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function removeItem (Request $request, string $supplier) : JsonResponse
	{

	}

	/**
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function retrieveItems (Request $request, string $supplier) : JsonResponse
	{

	}
	
	/**
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function addPassengers (Request $request, string $supplier) : JsonResponse
	{

	}

	/**
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function book (Request $request, string $supplier) : JsonResponse
	{

	}
	
	/**
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function listBookings (Request $request, string $supplier) : JsonResponse
	{

	}

	/**
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function retrieveBooking (Request $request, string $supplier) : JsonResponse
	{

	}
	
	/**
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function cancelBooking (Request $request, string $supplier) : JsonResponse
	{

	}



}
