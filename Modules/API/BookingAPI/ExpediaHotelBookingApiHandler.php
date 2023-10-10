<?php

namespace Modules\API\BookingAPI;

use Modules\API\BaseController;
use App\Models\ApiSearchInspector;
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
use Modules\API\Suppliers\ExpediaSupplier\RapidClient;
use Modules\Inspector\BookingInspectorController;

class ExpediaHotelBookingApiHandler
{
	private $experiaService;
	private $bookingInspector;
	private $rapidClient;

	public function __construct(ExperiaService $experiaService)
	{
		$this->experiaService = $experiaService;
		$this->bookingInspector = new BookingInspectorController();
		$this->rapidClient = new RapidClient(env('EXPEDIA_RAPID_API_KEY'), env('EXPEDIA_RAPID_SHARED_SECRET'));
	}
	/**
	 * @param Request $request
	 * @return array|null
	 */
	public function addItem(Request $request, array $filters): array|null
	{
		# step 1 Read Inspector, Get linck 'price_check'
		$inspector = new ApiSearchInspector();
		$linkPriceCheck = $inspector->getLinckPriceCheck($filters);

		# step 2 Get POST linck for booking
		// TODO: need check if price chenged
		$props = $this->getPathParamsFromLink($linkPriceCheck);
		$response = $this->rapidClient->get($props['path'], $props['paramToken']);
		$dataResponse = json_decode($response->getBody()->getContents());

		if (!$dataResponse) return [];

		$this->bookingInspector->save($filters, $dataResponse, 1, 'add_item', 'price_check');

		$linckBookItineraries =  $dataResponse->links->book->href;

		# Booking POST query 
		$props = $this->getPathParamsFromLink($linckBookItineraries);
		$bodyArr = $filters['query'];
		$bodyArr['affiliate_reference_id'] = 'UJV_'.time();
		$body = json_encode($bodyArr);	
		$addHeaders = [
			'Customer-Ip' => '5.5.5.5',
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
			'Test' => 'standard'
		];
		$response = $this->rapidClient->post($props['path'], $props['paramToken'], $body, $addHeaders);
		$dataResponse = json_decode($response->getBody()->getContents());

		if (!$dataResponse) return [];
		$this->bookingInspector->save($filters, $dataResponse, 1, 'add_item', 'post_book');

		$itinerary_id = $dataResponse->itinerary_id;
		$linckBookRetrieves =  $dataResponse->links->retrieve->href;

		// dump($itinerary_id, $linckBookRetrieves);

		# Booking POST query 
		$props = $this->getPathParamsFromLink($linckBookRetrieves);
		$addHeaders = [
			'Customer-Ip' => '5.5.5.5',
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
			'Test' => 'standard'
		];
		$response = $this->rapidClient->get($props['path'], $props['paramToken'], $addHeaders);
		$dataResponse = json_decode($response->getBody()->getContents());

		if (!$dataResponse) return [];
		$this->bookingInspector->save($filters, $dataResponse, 1, 'add_item', 'get_book');

		// dd($dataResponse);

		return (array)$dataResponse;
	}

	private function getPathParamsFromLink(string $link): array
	{
		$arr_link = explode('?', $link);
		$path = $arr_link[0];
		$arr_param = explode('=', $arr_link[1]);
		$paramToken = [$arr_param[0] => str_replace('token=', '', $arr_link[1])];

		return ['path' => $path, 'paramToken' => $paramToken];
	}

	/**
	 * @param Request $request
	 * @return array|null
	 */
	public function removeItem(Request $request): array|null
	{
	}

	/**
	 * @param Request $request
	 * @return array|null
	 */
	public function retrieveItems(Request $request): array|null
	{
	}

	/**
	 * @param Request $request
	 * @return array|null
	 */
	public function addPassengers(Request $request): array|null
	{
	}

	/**
	 * @param Request $request
	 * @return array|null
	 */
	public function book(Request $request): array|null
	{
	}

	/**
	 * @param Request $request
	 * @return array|null
	 */
	public function listBookings(Request $request): array|null
	{
	}

	/**
	 * @param Request $request
	 * @return array|null
	 */
	public function retrieveBooking(Request $request): array|null
	{
	}

	/**
	 * @param Request $request
	 * @return array|null
	 */
	public function cancelBooking(Request $request): array|null
	{
	}
}
