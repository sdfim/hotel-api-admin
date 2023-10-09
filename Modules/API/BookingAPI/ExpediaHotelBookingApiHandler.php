<?php

namespace Modules\API\BookingAPI;

use Modules\API\BaseController;
use App\Models\ApiInspector;
use Modules\API\Requests\SearchHotelRequest;
use Modules\API\Requests\DetailHotelRequest;
use Modules\API\Requests\PriceHotelRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\API\ContentAPI\Controllers\HotelSearchBuilder;
use Modules\API\Suppliers\ExpediaSupplier\ExperiaService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Modules\Inspector\InspectorController;

class ExpediaHotelBookingApiHandler
{
	private $experiaService;
	private $apiInspector;

	public function __construct(ExperiaService $experiaService) {
		$this->experiaService = $experiaService;
		$this->apiInspector = new InspectorController();
	}
	/**
	 * @param Request $request
	 * @return array|null
	 */
	public function addItem (Request $request, array $filters) : array|null
	{
		$uuid = $filters['inspector'];
		$hotel_code = $filters['hotel_code']; // giata_id
		$room_code = $filters['room_code']; // expedia
		$rate_code = $filters['rate'] ?? ''; // expedia
		$bed_groups = $filters['bed_groups'] ?? ''; // expedia

		$inspector = ApiInspector::where('id', $uuid)->first();
		$json_response = json_decode(Storage::get($inspector->response_path));
		$rooms = $json_response->results->Expedia->$hotel_code->rooms;

		$link = '';
		foreach ($rooms as $room) {
			if ($room->id == $room_code) {
				$rates = $room->rates;
				foreach ($rates as $rate) {
					if ($rate->id == $rate_code) {
						$link = $rate->bed_groups->$bed_groups->links->price_check->href;
					}
				}
				break;
			}
		}
		dd($inspector->response_path, $json_response, $link);

		return [];
	}

	/**
	 * @param Request $request
	 * @return array|null
	 */
	public function removeItem (Request $request) : array|null
	{

	}

	/**
	 * @param Request $request
	 * @return array|null
	 */
	public function retrieveItems (Request $request) : array|null
	{

	}
	
	/**
	 * @param Request $request
	 * @return array|null
	 */
	public function addPassengers (Request $request) : array|null
	{

	}

	/**
	 * @param Request $request
	 * @return array|null
	 */
	public function book (Request $request) : array|null
	{

	}
	
	/**
	 * @param Request $request
	 * @return array|null
	 */
	public function listBookings (Request $request) : array|null
	{

	}

	/**
	 * @param Request $request
	 * @return array|null
	 */
	public function retrieveBooking (Request $request) : array|null
	{

	}
	
	/**
	 * @param Request $request
	 * @return array|null
	 */
	public function cancelBooking (Request $request) : array|null
	{

	}



}
