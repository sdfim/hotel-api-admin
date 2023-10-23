<?php

namespace Modules\API\BookingAPI\BookingApiHendlers;

use Modules\API\BaseController;
use App\Models\ExpediaContent;
use App\Models\MapperExpediaGiata;
use App\Models\Supplier;
use Modules\API\BookingApi\BookingApiHandlerInterface;
use Modules\API\Requests\BookingAddItemHotelRequest;
use Modules\API\Requests\SearchHotelRequest;
use Modules\API\Requests\DetailHotelRequest;
use Modules\API\Requests\PriceHotelRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\API\ContentAPI\Controllers\HotelSearchBuilder;
use Modules\API\Suppliers\ExpediaSupplier\ExperiaService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Modules\Inspector\SearchInspectorController;
use Modules\API\BookingAPI\ExpediaHotelBookingApiHandler;
use Termwind\Components\Raw;

class HotelBookingApiHanlder extends BaseController // implements BookingApiHandlerInterface
{
	private $experiaService;
	private $apiInspector;
	private $expedia;
	private const EXPEDIA_SUPPLIER_NAME = 'Expedia';
	public function __construct(ExperiaService $experiaService) {
		$this->experiaService = $experiaService;
		$this->apiInspector = new SearchInspectorController();
		$this->expedia = new ExpediaHotelBookingApiHandler($this->experiaService);

	}
	/**
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function addItem (Request $request, string $supplier) : JsonResponse
	{
		$data = [];
		try {
			$bookingAddItemRequest = new BookingAddItemHotelRequest();
			$rules = $bookingAddItemRequest->rules();
			$filters = Validator::make($request->all(), $rules)->validated();
			$filters = array_merge($filters, $request->all());

			$data = [];
			if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {
				$data = $this->expedia->addItem($filters);
			}
			// TODO: Add other suppliers

		} catch (\Exception $e) {
			\Log::error('HotelBookingApiHanlder | addItem ' . $e->getMessage());
			return $this->sendError(['error' => $e->getMessage()], 'falied');
		}

		if (isset($data['errors'])) return $this->sendError($data['errors'], $data['message']);
		return $this->sendResponse($data, 'success');
	}

	/**
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function removeItem (Request $request, string $supplier) : JsonResponse
	{
		try {
			// TODO: add validation for request
			$filters = $request->all();

			$data = [];
			if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {
				$data = $this->expedia->removeItem($filters);
			}
			// TODO: Add other suppliers

		} catch (\Exception $e) {
			\Log::error('HotelBookingApiHanlder | removeItem ' . $e->getMessage());
			return $this->sendError(['error' => $e->getMessage()], 'falied');
		}

		if (isset($data['error'])) return $this->sendError($data['error']);

		return $this->sendResponse(['result' => $data['success']], 'success');
	}

	/**
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function retrieveItems (Request $request, string $supplier) : JsonResponse
	{
		try {
			// TODO: add validation for request
			$filters = $request->all();

			$data = [];
			if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {
				$data = $this->expedia->retrieveItems($filters);
			}
			// TODO: Add other suppliers

		} catch (\Exception $e) {
			\Log::error('HotelBookingApiHanlder | retrieveItems ' . $e->getMessage());
			return $this->sendError(['error' => $e->getMessage()], 'falied');
		}

		return $this->sendResponse(['result' => $data], 'success');

	}

	/**
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function addPassengers (Request $request, string $supplier) : JsonResponse
	{
		try {
			// TODO: add validation for request
			$filters = $request->all();

			$data = [];
			if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {
				$data = $this->expedia->addPassengers($filters);
			}
			// TODO: Add other suppliers

		} catch (\Exception $e) {
			\Log::error('HotelBookingApiHanlder | listBookings ' . $e->getMessage());
			return $this->sendError(['error' => $e->getMessage()], 'falied');
		}

		return $this->sendResponse(['count' => count($data), 'result' => $data], 'success');

	}

	/**
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function changeItems (Request $request, string $supplier) : JsonResponse
	{
		try {
			// TODO: add validation for request
			$filters = $request->all();

			$data = [];
			if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {
				$data = $this->expedia->changeItems($filters);
			}
			// TODO: Add other suppliers

		} catch (\Exception $e) {
			\Log::error('HotelBookingApiHanlder | listBookings ' . $e->getMessage());
			return $this->sendError(['error' => $e->getMessage()], 'falied');
		}

		if (isset($data['errors'])) return $this->sendError($data['errors'], $data['message']);
		return $this->sendResponse($data, 'success');

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
		try {
			// TODO: add validation for request
			$filters = $request->all();

			$data = [];
			if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {
				$data = $this->expedia->listBookings();
			}
			// TODO: Add other suppliers

		} catch (\Exception $e) {
			\Log::error('HotelBookingApiHanlder | listBookings ' . $e->getMessage());
			return $this->sendError(['error' => $e->getMessage()], 'falied');
		}

		return $this->sendResponse(['count' => count($data), 'result' => $data], 'success');

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
		try {
			// TODO: add validation for request
			$filters = $request->all();

			$data = [];
			if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {
				$data = $this->expedia->cancelBooking($filters);
			}
			// TODO: Add other suppliers

		} catch (\Exception $e) {
			\Log::error('HotelBookingApiHanlder | removeItem ' . $e->getMessage());
			return $this->sendError(['error' => $e->getMessage()], 'falied');
		}

		if (isset($data['error'])) return $this->sendError($data['error']);

		return $this->sendResponse(['result' => $data['success']], 'success');

	}



}
