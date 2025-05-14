<?php

namespace Modules\API\Suppliers\ExpediaSupplier;

use App\Models\ApiBookingItem;
use App\Models\ApiSearchInspector;
use App\Models\Channel;
use App\Models\Reservation;
use App\Models\Supplier;
use App\Repositories\ApiSearchInspectorRepository as SearchRepository;
use App\Repositories\ChannelRepository;
use App\Repositories\ExpediaContentRepository as ExpediaRepository;
use Exception;
use Illuminate\Support\Facades\Log;
use Modules\Enums\SupplierNameEnum;
use Modules\Enums\TypeRequestEnum;

class ExpediaTools
{
    public function saveAddItemToReservations(string $booking_id, array $filters, array $passenger): void
    {
        try {
            $token = request()->bearerToken() ?? config('booking-suppliers.Expedia.credentials.test_token');
            $token_id = ChannelRepository::getTokenId($token);
            $channel_id = Channel::where('token_id', $token_id)->first()?->id;

            $apiSearchInspector = ApiSearchInspector::where('search_id', $filters['search_id'])->first();
            $search_type = $apiSearchInspector->search_type;

            $apiBookingItem = ApiBookingItem::where('booking_item', $filters['booking_item'])->first();
            $supplier = Supplier::where('id', $apiBookingItem->supplier_id)->first()->name;

            foreach ($passenger['rooms'] as $room) {
                foreach ($room as $passenger) {
                    $passenger_surname = ($passenger['family_name'] ?? '').' '.($passenger['given_name'] ?? '');
                }
            }

            $hotelId = null;
            $hotelName = null;
            $hotelImages = null;
            $checkin = null;
            $totalCost = null;
            if (SupplierNameEnum::from($supplier) === SupplierNameEnum::HBSI
                || SupplierNameEnum::from($supplier) === SupplierNameEnum::EXPEDIA) {
                $reservationsData = SearchRepository::getReservationsData($apiBookingItem, $apiSearchInspector);
                $hotelName = ! is_null($reservationsData['expedia_hotel_id']) ? ExpediaRepository::getHotelNameByHotelId($reservationsData['expedia_hotel_id']) : '';
                $hotelImages = ! is_null($reservationsData['expedia_hotel_id']) ? ExpediaRepository::getHotelImagesByHotelId($reservationsData['expedia_hotel_id']) : '';
                $hotelId = $reservationsData['hotel_id'];
                $checkin = $reservationsData['query']['checkin'];
                $totalCost = $reservationsData['price']['total_price'];
            }

            if (TypeRequestEnum::from($search_type) === TypeRequestEnum::HOTEL) {
                $reservation = new Reservation();

                $reservation->date_offload = null;
                $reservation->date_travel = date('Y-m-d', strtotime($checkin));
                $reservation->passenger_surname = $passenger_surname;
                $reservation->reservation_contains = json_encode([
                    'type' => $search_type,
                    'supplier' => $supplier,
                    'booking_id' => $booking_id,
                    'booking_item' => $filters['booking_item'],
                    'search_id' => $filters['search_id'],
                    'hotel_id' => $hotelId,
                    'hotel_name' => $hotelName,
                    'hotel_images' => json_encode($hotelImages),
                    'price' => json_decode($apiBookingItem->booking_pricing_data, true),
                ]);

                $reservation->channel_id = $channel_id;
                $reservation->total_cost = $totalCost;
                $reservation->canceled_at = null;

                $reservation->save();
            }

        } catch (Exception $e) {
            Log::error('ExpediaTools | saveAddItemToReservations'.$e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }
}
