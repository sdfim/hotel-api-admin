<?php

namespace Modules\API\Suppliers\ExpediaSupplier;

use App\Models\Channels;
use Illuminate\Support\Facades\Cache;
use App\Models\Reservations;
use App\Models\ApiSearchInspector;
use App\Models\ExpediaContent;

class ExpediaTools
{
	private $apiInspector;
	private $channel;
	private $expedia;
	public function __construct() {
		$this->apiInspector = new ApiSearchInspector();
		$this->channel = new Channels();
		$this->expedia = new ExpediaContent();
	}

	public function saveAddItemToReservations(string $booking_id, array $filters) : void
	{
		try {
			$token_id = $this->channel->getTokenId(request()->bearerToken());
			$channel_id = Channels::where('token_id', $token_id)->first()->id;

			$reservationsData = $this->apiInspector->getReservationsDataBySearchId($filters);

			$checkin = $reservationsData['query']->checkin;

			$hotel_id = $filters['hotel_id'];
			$hotelName = $this->expedia->getHotelNameByHotelId($reservationsData['supplier_hotel_id']);
			$hotelImages = $this->expedia->getHotelImagesByHotelId($reservationsData['supplier_hotel_id']);

			$reservation = new Reservations();
			$reservation->date_offload = null;
			$reservation->date_travel = date("Y-m-d", strtotime($checkin));
			$reservation->passenger_surname = $filters['query']['rooms'][0]['family_name'];
			$reservation->reservation_contains = json_encode([
				'type' => 'hotel',
				'supplier' => 'Expedia',
				'booking_id' => $booking_id,
				'hotel_id' => $hotel_id,
				'hotel_name' => $hotelName,
				'hotel_images' => json_encode($hotelImages),
			]);
			$reservation->channel_id = $channel_id;

			$reservation->total_cost = $reservationsData['price']['total_price'];

			$reservation->canceled_at = null;

			$reservation->save();
		} catch (\Exception $e) {
			\Log::error('ExpediaTools | saveAddItemToReservations' . $e->getMessage());
		}
		
	}
}