<?php

namespace Modules\API\BookingAPI;

use App\Jobs\SaveBookingInspector;
use App\Models\ApiBookingInspector;
use App\Models\ApiBookingItem;
use App\Models\ApiSearchInspector;
use App\Models\Channel;
use DB;
use Exception;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\API\Suppliers\ExpediaSupplier\ExpediaService;
use Modules\API\Suppliers\ExpediaSupplier\RapidClient;

class ExpediaHotelBookingApiHandler
{
    /**
     * @var RapidClient
     */
    private RapidClient $rapidClient;

    /**
     * @param ExpediaService $expediaService
     */
    public function __construct()
    {
        $this->rapidClient = new RapidClient();
    }

    /**
     * @param array $filters
     * @return array|null
     */
    public function addItem(array $filters): array | null
    {
        # step 1 Read Inspector, Get link 'price_check'
        $inspector = new ApiSearchInspector();
        $linkPriceCheck = $inspector->getLinckPriceCheck($filters);

        # step 2 Get POST link for booking
        // TODO: need check if price changed
        $props = $this->getPathParamsFromLink($linkPriceCheck);
        try {
            $response = $this->rapidClient->get($props['path'], $props['paramToken']);
            $dataResponse = json_decode($response->getBody()->getContents());
        } catch (RequestException $e) {
            \Log::error('ExpediaHotelBookingApiHandler | addItem | price_check ' . $e->getResponse()->getBody());
            $dataResponse = json_decode('' . $e->getResponse()->getBody());
            return (array) $dataResponse;
        }

        if (!$dataResponse) {
            return [];
        }

        if (isset($filters['booking_id'])) {
            $booking_id = $filters['booking_id'];
        } else {
            $booking_id = (string) Str::uuid();
        }

        SaveBookingInspector::dispatch([
            $booking_id,
            $filters,
            $dataResponse,
            [],
            1,
            'add_item',
            'price_check',
            'hotel',
        ]);

        return ['booking_id' => $booking_id];
    }

    /**
     * @param string $link
     * @return array
     */
    private function getPathParamsFromLink(string $link): array
    {
        $arr_link = explode('?', $link);
        $path = $arr_link[0];
        $arr_param = explode('=', $arr_link[1]);
        $paramToken = [$arr_param[0] => str_replace('token=', '', $arr_link[1])];

        return ['path' => $path, 'paramToken' => $paramToken];
    }

    /**
     * @param array $filters
     * @return array
     */
    public function removeItem(array $filters): array
    {
        $filters['search_id'] = ApiBookingInspector::where('booking_id', $filters['booking_id'])->first()->search_id;
        $booking_id = $filters['booking_id'];
		$booking_item = $filters['booking_item'];

		try {

			$bookItems = ApiBookingInspector::where('booking_id', $booking_id)
				->where('type', 'book')
				->get()->pluck('booking_id')->toArray();

			$bookingItems = ApiBookingInspector::where('booking_item', $booking_item)
				->where('type', 'add_item')
				->whereNotIn('booking_id', $bookItems);

			if ($bookingItems->get()->count() === 0) {
				$res = [
					'success' =>
						[
							'booking_id' => $booking_id,
							'booking_item' => $booking_item,
							'status' => 'This item is not in the cart',
						]
					];
			} else {
				$bookingItems->delete();
				$res = [
					'success' =>
						[
							'booking_id' => $booking_id,
							'booking_item' => $booking_item,
							'status' => 'Item removed from cart.',
						]
					];
			}				
		} catch (Exception $e) {
			$res =  [
				'error' => [
					'booking_id' => $booking_id,
					'booking_item' => $booking_item,
					'status' => 'Item not removed from cart.',
				]
			];
			\Log::error('ExpediaHotelBookingApiHandler | removeItem | ' . $e->getMessage());
		}	
		
		SaveBookingInspector::dispatch([
            $booking_id,
            $filters,
            [],
            $res,
            1,
            'remove_item',
            '',
            'hotel',
        ]);

		return $res;
    }

    /**
     * @param array $filters
     * @return array|null
     */
    public function retrieveItems(array $filters): array | null
    {
        $booking_id = $filters['booking_id'];
		$filters['search_id'] = ApiBookingInspector::where('booking_id', $filters['booking_id'])->first()->search_id;

		try {
			$bookItems = ApiBookingInspector::where('booking_id', $booking_id)
				->where('type', 'book')
				->get()->pluck('booking_id')->toArray();

			$bookingItems = ApiBookingInspector::where('booking_id', $booking_id)
				->where('type', 'add_item')
				->whereNotIn('booking_id', $bookItems)
				->get()->pluck('booking_item')->toArray();

			$responseBookingItems = [];
			foreach ($bookingItems as $item) {
				$apiBookingItem = ApiBookingItem::where('booking_item', $item)->first();
				$search_id = $apiBookingItem->search_id;
				$booking_item_data = json_decode($apiBookingItem->booking_item_data, true);
				
				$client_response_path = ApiSearchInspector::where('search_id', $search_id)->first()->client_response_path;
				$client_response = json_decode(Storage::get($client_response_path), true);
				$response = [];
				foreach ($client_response['results']['Expedia'] as $value) {
					if ($value['giata_hotel_id'] === $booking_item_data['hotel_id']) {
						foreach ($value['room_groups'] as $keyGroup => $room_group) {
							foreach ($room_group['rooms'] as $key => $room) {
								if ($room['booking_item'] == $item) {
									$response = $room;
								}
							}
						}
					}
				}
				$responseBookingItems[] = [
					'booking_item' => $item,
					'booking_item_data' => $booking_item_data,
					'room' => $response,
				];
			}

			$res = [
				'success' =>
					[
						'booking_id' => $booking_id,
						'booking_items' => $responseBookingItems,
					]
				];
		} catch (Exception $e) {
			$res =  [
				'error' => [
					'booking_id' => $booking_id,
				],
				'message' => 'Booking not found',
			];
		}

        SaveBookingInspector::dispatch([
            $booking_id,
            $filters,
            [],
            $res,
            1,
            'retrieve-items',
            '',
            'hotel',
        ]);

        return $res;
    }

    /**
     * @param array $filters
     * @return array|null
     */
    public function addPassengers(array $filters): array | null
    {
		$booking_id = $filters['booking_id'];
		$filters['search_id'] = ApiBookingInspector::where('booking_id', $filters['booking_id'])->first()->search_id;

		$bookingItem = ApiBookingInspector::where('booking_id', $booking_id)
			->where('booking_item', $filters['booking_item'])
			->where('type', 'add_passengers');

		$apiSearchInspector = ApiSearchInspector::where('search_id', $filters['search_id'])->first()->request;

		$countRooms = count(json_decode($apiSearchInspector, true)['occupancy']);

		if ($countRooms != count($filters['rooms'])) {
			$res = [
				'error' => [
					'booking_id' => $booking_id,
					'booking_item' => $filters['booking_item'],
					'status' => 'The number of rooms does not match the number of rooms in the search. Must be ' . $countRooms . ' rooms.',
				]
			];
			return $res;
		}

		if ($bookingItem->get()->count() > 0) {
			$bookingItem->delete();
			$status = 'Passengers updated to booking.';
			$subType = 'updated';
		} else {
			$status = 'Passengers added to booking.';
			$subType = 'add';
		}

		$res = [
			'success' =>
				[
					'booking_id' => $booking_id,
					'booking_item' => $filters['booking_item'],
					'status' => $status,
				]
			];

		SaveBookingInspector::dispatch([
			$booking_id,
			$filters,
			[],
			$res,
			1,
			'add_passengers',
			$subType,
			'hotel',
		]);

		return $res;
    }

    /**
     * @param array $filters
     * @return array|null
     */
    public function changeBooking(array $filters): array | null
    {
        # step 1 Get room_id from ApiBookingItem
        $bookingItem = ApiBookingItem::where('booking_item', $filters['booking_item'])->first();
        $room_id = json_decode($bookingItem->booking_item_data, true)['room_id'];

        # step 2 Read Booking Inspector, Get link  PUT method from 'add_item | get_book'
        $inspector = new ApiBookingInspector();
        $linkPutMethod = $inspector->getLinkPutMethod($filters['booking_id'], $room_id);

        $search_id = $inspector->getSearchId($filters);
        $filters['search_id'] = $search_id;
        $booking_id = $filters['booking_id'];

        # Booking PUT query
        $props = $this->getPathParamsFromLink($linkPutMethod);

        $addHeaders = [
            'Customer-Ip' => '5.5.5.5',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Test' => 'standard',
        ];

        $bodyArr = $filters['query'];
        $body = json_encode($bodyArr);

        try {
            $response = $this->rapidClient->put($props['path'], $props['paramToken'], $body, $addHeaders);
            $dataResponse = json_decode($response->getBody()->getContents());
        } catch (RequestException $e) {
            \Log::error('ExpediaHotelBookingApiHandler | addPassengers | Booking PUT query ' . $e->getResponse()->getBody());
            $dataResponse = json_decode('' . $e->getResponse()->getBody());
        }

        if (!$dataResponse) {
            return [];
        }

        SaveBookingInspector::dispatch([
            $booking_id,
            $filters,
            $dataResponse,
            $dataResponse,
            1,
            'change_booking',
            '',
            'hotel',
        ]);

        return (array) $dataResponse;
    }

    /**
     * @param Request $request
     * @return array|null
     */
    public function book(array $filters): array | null
    {
        $booking_id = $filters['booking_id'];
        $bookLinks = ApiBookingInspector::where('type', 'add_item')
            ->where('sub_type', 'like', 'price_check' . '%')
            ->where('booking_id', $booking_id)
            ->get();

        foreach ($bookLinks as $bookLink) {

			$queryHold = $filters['query']['hold'] ?? false;

            $dataResponse = json_decode(Storage::get($bookLink->response_path));

            $linkBookItineraries = $dataResponse->links->book->href;
            $filters['search_id'] = $bookLink->search_id;
            $filters['booking_item'] = $bookLink->booking_item;

			$passengers = ApiBookingInspector::where('booking_id', $booking_id)
				->where('booking_item', $filters['booking_item'])
				->where('type', 'add_passengers')
				->first()
				->toArray();

			$dataPassengers = json_decode($passengers['request'], true);
			// dd($dataPassengers);

            # Booking POST query - Create Booking
            // TODO: need check count of rooms. count(rooms) in current query == count(rooms) in search query
            $props = $this->getPathParamsFromLink($linkBookItineraries);

            // $bodyArr = $filters['query'];
			$bodyArr = $dataPassengers;

            $bodyArr['affiliate_reference_id'] = 'UJV_' . time();

			// TODO: need move it to config or const
			foreach ($bodyArr['payments'] as $key => $payment) {
				$bodyArr['payments'][$key]['type'] = 'affiliate_collect';
			}

            $body = json_encode($bodyArr);
            $addHeaders = [
                'Customer-Ip' => '5.5.5.5',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Test' => 'standard',
            ];
            try {
                $response = $this->rapidClient->post($props['path'], $props['paramToken'], $body, $addHeaders);
                $dataResponse = json_decode($response->getBody()->getContents());
            } catch (RequestException $e) {
                \Log::error('ExpediaHotelBookingApiHandler | addItem | create ' . $e->getResponse()->getBody());
                $dataResponse = json_decode('' . $e->getResponse()->getBody());
                return (array) $dataResponse;
            }

            if (!$dataResponse) {
                return [];
            }

            SaveBookingInspector::dispatch([
                $booking_id,
                $filters,
                $dataResponse,
                [],
                1,
                'book',
                'create' . ($queryHold ? ':hold' : ''),
                'hotel',
            ]);

            $itinerary_id = $dataResponse->itinerary_id;
            $linkBookRetrieves = $dataResponse->links->retrieve->href;

            # Booking GET query - Retrieve Booking
            $props = $this->getPathParamsFromLink($linkBookRetrieves);
            try {
                $response = $this->rapidClient->get($props['path'], $props['paramToken'], $addHeaders);
                $dataResponse = json_decode($response->getBody()->getContents());
            } catch (RequestException $e) {
                \Log::error('ExpediaHotelBookingApiHandler | addItem | create ' . $e->getResponse()->getBody());
                $dataResponse = json_decode('' . $e->getResponse()->getBody());
                return (array) $dataResponse;
            }

            if (!$dataResponse) {
                return [];
            }

            $viewSupplierData = $filters['supplier_data'] ?? false;
            if ($viewSupplierData) {
                $res[] = (array) $dataResponse;
            } else {
                $res[] = [
                    'booking_id' => $booking_id,
                    'search_id' => $filters['search_id'],
                    'booking_item' => $filters['booking_item'],
                    'links' => [
                        'remove' => [
                            'method' => 'DELETE',
                            'href' => '/api/booking/cancel-booking?booking_id=' . $booking_id . '&booking_item=' . $filters['booking_item'],
                        ],
                        'change' => [
                            'method' => 'PUT',
                            'href' => '/api/booking/change-booking?booking_id=' . $booking_id . '&booking_item=' . $filters['booking_item'],
                        ],
                        'retrieve' => [
                            'method' => 'GET',
                            'href' => '/api/booking/retrieve-booking?booking_id=' . $booking_id,
                        ],
                    ],
                ];
            }

            SaveBookingInspector::dispatch([
                $booking_id,
                $filters,
                $dataResponse,
                $res,
                1,
                'book',
                'retrieve' . ($queryHold ? ':hold' : ''),
                'hotel',
            ]);
        }

        return $res;
    }

    /**
     * @return array|null
     */
    public function listBookings(): array | null
    {
        $ch = new Channel;
        $token_id = $ch->getTokenId(request()->bearerToken());

        # step 1 Read Booking Inspector, Get link  GET method from 'add_item | post_book'
        $inspector = new ApiBookingInspector();
        $list = $inspector->getAffiliateReferenceIdByChannel($token_id);
        $path = '/v3/itineraries';
        $addHeaders = [
            'Customer-Ip' => '5.5.5.5',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Test' => 'standard',
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
    public function retrieveBooking(array $filters): array | null
    {
        $booking_id = $filters['booking_id'];

        $inspector = new ApiBookingInspector();
        $search_id = $inspector->getSearchId($filters);
        $filters['search_id'] = $search_id;

        # step 1 Read Booking Inspector, Get link  GET method from 'add_item | post_book'
        $inspectorLinks = ApiBookingInspector::where('type', 'book')
            ->where('sub_type', 'like', 'create' . '%')
            ->where('booking_id', $booking_id)
            ->get();

		$dataResponse = [];
        foreach ($inspectorLinks as $item) {
            $json_response = json_decode(Storage::get($item->response_path));

            $linkRetrieveItem = $json_response->links->retrieve->href;

            # Booking GET query
            $props = $this->getPathParamsFromLink($linkRetrieveItem);
            $addHeaders = [
                'Customer-Ip' => '5.5.5.5',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Test' => 'standard',
            ];
            $response = $this->rapidClient->get($props['path'], $props['paramToken'], $addHeaders);
            $dataResponse[] = json_decode($response->getBody()->getContents());

            // TODO: need create DTO for $clientDataResponse
            $clientDataResponse = $dataResponse;
        }

		SaveBookingInspector::dispatch([
			$booking_id,
			$filters,
			$dataResponse,
			$clientDataResponse,
			1,
			'retrieve_booking',
			'',
			'hotel',
		]);

        return (array) $dataResponse;
    }

    /**
     * @param array $filters
     * @return array|null
     */

    public function cancelBooking(array $filters): array | null
    {
        if (isset($filters['booking_item'])) {
            $bookingItems[] = $filters['booking_item'];
        } else {
            $bookingItems = ApiBookingInspector::where('booking_id', $filters['booking_id'])
				->where('type', 'book')	
				->where('sub_type', 'like', 'create' . '%')	
				->get()->pluck('booking_item')->toArray();
        }

        $res = ['booking_id' => $filters['booking_id']];
		$dataResponse = [];
        foreach ($bookingItems as $item) {

            if ($item === null) {
                continue;
            }

            # step 1 Get room_id from ApiBookingItem
            $bookingItem = ApiBookingItem::where('booking_item', $item)->first();
            $room_id = json_decode($bookingItem->booking_item_data, true)['room_id'];

            # step 2 Read Booking Inspector, Get link  DELETE method from 'add_item | get_book'
            $inspector = new ApiBookingInspector();
            $linkDeleteItem = $inspector->getLinkDeleteItem($filters['booking_id'], $room_id);

            $search_id = $inspector->getSearchId($filters);
            $filters['search_id'] = $search_id;
            $booking_id = $filters['booking_id'];

            # Delete item DELETE method query
            $props = $this->getPathParamsFromLink($linkDeleteItem);

            $bodyArr = [
                'itinerary_id' => $inspector->getItineraryId($filters),
                'room_id' => $room_id,
            ];
            $body = json_encode($bodyArr);

            $addHeaders = [
                'Customer-Ip' => '5.5.5.5',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Test' => 'standard',
            ];
            try {
                $response = $this->rapidClient->delete($props['path'], $props['paramToken'], $body, $addHeaders);
                $dataResponse[] = json_decode($response->getBody()->getContents());

                $res[] = [
                    'booking_item' => $item,
                    'status' => 'Room cancelled.',
                ];

            } catch (Exception $e) {
                $responseError = explode('response:', $e->getMessage());
                $responseErrorArr = json_decode($responseError[1], true);
                $res[] = [
                    'booking_item' => $item,
                    'status' => $responseErrorArr['message'],
                ];
            }

			SaveBookingInspector::dispatch([
				$booking_id,
				$filters,
				$dataResponse,
				$res,
				1,
				'cancel_booking',
				'true',
				'hotel',
			]);
        }

        return ['success' => $res];
    }
}
