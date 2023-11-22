<?php

namespace Modules\API\BookingAPI;

use App\Jobs\SaveBookingInspector;
use App\Models\ApiBookingInspector;
use App\Models\ApiSearchInspector;
use Exception;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Str;
use Modules\API\Suppliers\ExpediaSupplier\ExpediaService;
use Modules\API\Suppliers\ExpediaSupplier\RapidClient;
use Illuminate\Support\Facades\Storage;


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
				foreach ($bookingItems->get() as $item)	{
					Storage::delete($item->client_response_path);
					Storage::delete($item->response_path);
				}
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
    public function addPassengers(array $filters): array | null
    {
		$booking_id = $filters['booking_id'];
		$filters['search_id'] = ApiBookingInspector::where('booking_item', $filters['booking_item'])->first()->search_id;

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
   
}
