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
use Modules\Inspector\SearchInspectorController;

class ComboBookingApiHandler extends BaseController implements BookingApiHandlerInterface
{
	/**
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function addItem (Request $request, string $supplier) : JsonResponse
	{
		return $this->sendResponse(['result' => 'addItem'], 'success');
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
