<?php

namespace Modules\API\BookingAPI;

use App\Models\ApiSearchInspector;
use App\Models\ApiBookingInspector;
use App\Models\Channels;
use Illuminate\Http\Request;
use Modules\API\ContentAPI\Controllers\HotelSearchBuilder;
use Modules\API\Suppliers\ExpediaSupplier\ExperiaService;
use Illuminate\Support\Facades\Validator;
use Modules\API\Suppliers\ExpediaSupplier\RapidClient;
use Modules\Inspector\BookingInspectorController;
use Illuminate\Support\Str;
use GuzzleHttp\Promise;


class ExpediaHotelBookingApiHandler
{
	private const AFFILIATE_REFERENCE_ID = 'UJV-V1';
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
	public function addItem(array $filters): array|null
	{
		$queryHold = $filters['query']['hold'];
		# step 1 Read Inspector, Get linck 'price_check'
		$inspector = new ApiSearchInspector();
		$linkPriceCheck = $inspector->getLinckPriceCheck($filters);

		# step 2 Get POST linck for booking
		// TODO: need check if price chenged
		$props = $this->getPathParamsFromLink($linkPriceCheck);
		$response = $this->rapidClient->get($props['path'], $props['paramToken']);
		$dataResponse = json_decode($response->getBody()->getContents());

		if (!$dataResponse) return [];
		$booking_id = (string) Str::uuid();

		$this->bookingInspector->save($booking_id, $filters, $dataResponse, [], 1, 'add_item', 'price_check' . ($queryHold ? ':hold' : ''));

		$linckBookItineraries =  $dataResponse->links->book->href;

		# Booking POST query - Create Booking
		// TODO: need check count of rooms. count(rooms) in current query == count(rooms) in serch query
		$props = $this->getPathParamsFromLink($linckBookItineraries);
		$bodyArr = $filters['query'];
		$bodyArr['affiliate_reference_id'] = 'UJV_' . time();
		$body = json_encode($bodyArr);
		$addHeaders = [
			'Customer-Ip' => '5.5.5.5',
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
			'Test' => 'standard'
		];
		try {
			$response = $this->rapidClient->post($props['path'], $props['paramToken'], $body, $addHeaders);
			$dataResponse = json_decode($response->getBody()->getContents());
		} catch (\Exception $e) {
			\Log::error('ExpediaHotelBookingApiHandler | addItem | Booking POST query ' . $e->getMessage());
		}
		
		if (!$dataResponse) return [];
		$this->bookingInspector->save($booking_id, $filters, $dataResponse, [], 1, 'add_item', 'create' . ($queryHold ? ':hold' : ''));

		$itinerary_id = $dataResponse->itinerary_id;
		$linckBookRetrieves =  $dataResponse->links->retrieve->href;

		# Booking GET query - Retrieve Booking
		$props = $this->getPathParamsFromLink($linckBookRetrieves);
		$addHeaders = [
			'Customer-Ip' => '5.5.5.5',
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
			'Test' => 'standard'
		];
		$response = $this->rapidClient->get($props['path'], $props['paramToken'], $addHeaders);
		$dataResponse = json_decode($response->getBody()->getContents());

		// TODO: need create DTO for $clientDataResponse
		$clientDataResponse = $dataResponse;

		if (!$dataResponse) return [];
		$this->bookingInspector->save($booking_id, $filters, $dataResponse, $clientDataResponse, 1, 'add_item', 'retrieve' . ($queryHold ? ':hold' : ''));

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
	public function removeItem(array $filters): array
	{
		# step 1 Read Booking Inspector, Get linck  DELETE method from 'add_item | get_book'
		$inspector = new ApiBookingInspector();
		$linkDeleteItem = $inspector->getLinckDeleteItem($filters);
		$search_id = $inspector->getSearchId($filters);
		$filters['search_id'] = $search_id;
		$booking_id = $filters['booking_id'];

		# Delete item DELETE method query 
		$props = $this->getPathParamsFromLink($linkDeleteItem);

		// dump($props, $linkDeleteItem);

		$bodyArr = [
			'itinerary_id' => $inspector->getItineraryId($filters),
			'room_id' => $filters['room_id']
		];
		$body = json_encode($bodyArr);

		$addHeaders = [
			'Customer-Ip' => '5.5.5.5',
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
			'Test' => 'standard'
		];
		try {
			$response = $this->rapidClient->delete($props['path'], $props['paramToken'], $body, $addHeaders);
			$dataResponse = json_decode($response->getBody()->getContents());

			if (!$dataResponse) $this->bookingInspector->save(
				$booking_id, $filters, $dataResponse, ['success' => 'Room cancelled.'], 1, 'remove_item', 'true'
			);
			return ['success' => 'Room cancelled.'];
		} catch (\Exception $e) {
			$responseError = explode('response:', $e->getMessage());
			$responseErrorArr = json_decode($responseError[1], true);
			$this->bookingInspector->save(
				$booking_id, $filters, $responseErrorArr, ['error' => 'Room is already cancelled.'], 1, 'remove_item', 'false'
			);
			return ['error' => $responseErrorArr['message']];
		}
	}

	/**
	 * @param Request $request
	 * @return array|null
	 */
	public function retrieveItems(array $filters): array|null
	{
		$booking_id = $filters['booking_id'];
		
		# step 1 Read Booking Inspector, Get linck  GET method from 'add_item | post_book'
		$inspector = new ApiBookingInspector();
		$linkDeleteItem = $inspector->getLinckRetrieveItem($booking_id);

		$search_id = $inspector->getSearchId($filters);
		$filters['search_id'] = $search_id;

		# Booking GET query 
		$props = $this->getPathParamsFromLink($linkDeleteItem);
		$addHeaders = [
			'Customer-Ip' => '5.5.5.5',
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
			'Test' => 'standard'
		];
		$response = $this->rapidClient->get($props['path'], $props['paramToken'], $addHeaders);
		$dataResponse = json_decode($response->getBody()->getContents());

		// TODO: need create DTO for $clientDataResponse
		$clientDataResponse = $dataResponse;

		if (!$dataResponse) return [];
		$this->bookingInspector->save($booking_id, $filters, $dataResponse, $clientDataResponse, 1, 'retrieve_items', '');

		// dd($dataResponse);

		return (array)$dataResponse;
	}

	/**
	 * @param Request $request
	 * @return array|null
	 */
	public function addPassengers(array $filters): array|null
	{
		# step 1 Read Booking Inspector, Get linck  DELETE method from 'add_item | get_book'
		$inspector = new ApiBookingInspector();
		$linkPutMetod = $inspector->getLinckPutMetod($filters);
		$search_id = $inspector->getSearchId($filters);
		$filters['search_id'] = $search_id;
		$booking_id = $filters['booking_id'];

		# Booking PUT query 
		$props = $this->getPathParamsFromLink($linkPutMetod);
		$addHeaders = [
			'Customer-Ip' => '5.5.5.5',
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
			'Test' => 'standard'
		];
		
		$bodyArr = $filters['query'];
		$body = json_encode($bodyArr);

		try {
			$response = $this->rapidClient->put($props['path'], $props['paramToken'], $body, $addHeaders);
			$dataResponse = json_decode($response->getBody()->getContents());
		} catch (\Exception $e) {
			\Log::error('ExpediaHotelBookingApiHandler | addPassengers | Booking PUT query ' . $e->getMessage());
		}
		

		dd($props, $filters, $response);

		// // TODO: need create DTO for $clientDataResponse
		// $clientDataResponse = $dataResponse;

		// if (!$dataResponse) return [];
		// $this->bookingInspector->save($booking_id, $filters, $dataResponse, $clientDataResponse, 1, 'retrieve_items', '');

		// dd($dataResponse);

		return (array)$dataResponse;
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
	public function listBookings(): array|null
	{	
		$ch = new Channels;
		$token_id = $ch->getTokenId(request()->bearerToken());

		# step 1 Read Booking Inspector, Get linck  GET method from 'add_item | post_book'
		$inspector = new ApiBookingInspector();
		$list = $inspector->getAffiliateReferenceIdByCannel($token_id);
		$path = '/v3/itineraries';
		$addHeaders = [
			'Customer-Ip' => '5.5.5.5',
			'Accept' => 'application/json',
			'Content-Type' => 'application/json',
			'Test' => 'standard'
		];

		foreach ($list as $item) {
			try {
				$promises[] = $this->rapidClient->getAsync($path, $item, $addHeaders);
			} catch (Exception $e) {
				\Log::error('Error while creating promise: ' . $e->getMessage());
			}
		}
		$responses = [];
		$resolvedResponses = Promise\Utils::settle($promises)->wait();
		foreach ($resolvedResponses as $response) {
			if ($response['state'] === 'fulfilled') {
				$data = $response['value']->getBody()->getContents();
				$responses[] = json_decode($data, true);
			} else {
				\Log::error('ExpediaHotelBookingApiHandler | listBookings  failed: ' . $response['reason']->getMessage());
			}
		}

		return $responses;

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
	public function cancelBooking(array $filters): array|null
	{
		
	}
}
