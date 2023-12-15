<?php

namespace Modules\API\Suppliers\ExpediaSupplier;

use App\Models\ApiBookingItem;
use App\Models\Channel;
use App\Models\Reservation;
use App\Models\ApiSearchInspector;
use App\Models\Supplier;
use App\Models\ExpediaContent;
use App\Repositories\ChannelRenository;
use Exception;
use App\Repositories\ApiSearchInspectorRepository as SearchRepository;
use App\Repositories\ExpediaContentRepositories as ExpediaRepository;
use Illuminate\Support\Facades\Log;

;

class ExpediaTools
{
    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * @param string $booking_id
     * @param array $filters
     * @return void
     */
    public function saveAddItemToReservations(string $booking_id, array $filters, array $passenger): void
    {
        try {
            $token_id = ChannelRenository::getTokenId(request()->bearerToken());
            $channel_id = Channel::where('token_id', $token_id)->first()->id;

			$apiSearchInspector = ApiSearchInspector::where('search_id', $filters['search_id'])->first();
			$search_type = $apiSearchInspector->search_type;

			$apiBookingItem = ApiBookingItem::where('booking_item', $filters['booking_item'])->first();
			$supplier = Supplier::where('id', $apiBookingItem->supplier_id)->first()->name;

			$passenger_surname = ($passenger['rooms'][0][0]['family_name'] ?? '') . ' ' .
				($passenger['rooms'][0][0]['given_name'] ?? '');

			if ($supplier === 'Expedia') {
				$reservationsData = SearchRepository::getReservationsExpediaData($filters, $apiBookingItem, $apiSearchInspector);

				if ($search_type == 'hotel') {

					$reservation = new Reservation();

					$checkin = $reservationsData['query']['checkin'];
					$hotelName = ExpediaRepository::getHotelNameByHotelId($reservationsData['supplier_hotel_id']);
					$hotelImages = ExpediaRepository::getHotelImagesByHotelId($reservationsData['supplier_hotel_id']);

					$reservation->date_offload = null;
					$reservation->date_travel = date("Y-m-d", strtotime($checkin));
					$reservation->passenger_surname = $passenger_surname;
					$reservation->reservation_contains = json_encode([
						'type' => $search_type,
						'supplier' => $supplier,
						'booking_id' => $booking_id,
						'booking_item' => $filters['booking_item'],
						'search_id' => $filters['search_id'],
						'hotel_id' => $reservationsData['supplier_hotel_id'],
						'hotel_name' => $hotelName,
						'hotel_images' => json_encode($hotelImages),
						'price' => json_decode($apiBookingItem->booking_pricing_data, true),
					]);

					$reservation->channel_id = $channel_id;
					$reservation->total_cost = $reservationsData['price']['total_price'];
					$reservation->canceled_at = null;

					$reservation->save();
				}
				// TODO: add other search types
				else return;
			}
			// TODO: add other suppliers

        } catch (Exception $e) {
            Log::error('ExpediaTools | saveAddItemToReservations' . $e->getMessage());
        }
    }
}
