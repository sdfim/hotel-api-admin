<?php

namespace Modules\API\Tools;

use App\Models\ApiBookingItem;
use App\Models\ApiSearchInspector;
use App\Models\Channel;
use App\Models\Reservation;
use App\Models\Supplier;
use App\Repositories\ApiSearchInspectorRepository as SearchRepository;
use App\Repositories\ChannelRepository;
use App\Services\AdvisorCommissionService;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Modules\Enums\TypeRequestEnum;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Services\HotelService;

class ReservationTools
{
    public function saveAddItemToReservations(string $booking_id, array $filters, array $passenger, ?string $token = null): void
    {
        try {
            $token_id = ChannelRepository::getTokenId($token);
            $channel_id = Channel::where('token_id', $token_id)->first()?->id;

            $apiSearchInspector = ApiSearchInspector::where('search_id', $filters['search_id'])->first();
            $search_type = $apiSearchInspector->search_type;

            $apiBookingItem = ApiBookingItem::where('booking_item', $filters['booking_item'])->first();
            $supplier = Supplier::where('id', $apiBookingItem->supplier_id)->first()->name;

            if (! empty($passenger['rooms'][0])) {
                $firstRoom = $passenger['rooms'][0];
                if (! empty($firstRoom[0])) {
                    $firstPassenger = $firstRoom[0];
                    $passenger_surname = ($firstPassenger['family_name'] ?? '').' '.($firstPassenger['given_name'] ?? '');
                }
            }

            $reservationsData = SearchRepository::getReservationsData($apiBookingItem, $apiSearchInspector);

            $hotelId = $reservationsData['hotel_id'];

            $hotelName = Hotel::where('giata_code', $hotelId)->first()?->product->name ?? '';
            $hotelData = app(HotelService::class)->getDetailRespose($hotelId);
            $hotelImages = [];
            $images = Arr::get($hotelData, 'images', []);
            if (is_array($images)) {
                foreach ($images as $image) {
                    if (is_string($image)) {
                        $hotelImages[] = $image;
                    } elseif (is_array($image)) {
                        $hotelImages[] = Arr::get($image, 'url');
                    }
                    if (count($hotelImages) >= 5) {
                        break;
                    }
                }
            }

            $checkin = $reservationsData['query']['checkin'];
            $totalCost = $reservationsData['price']['total_price'];

            if (TypeRequestEnum::from($search_type) === TypeRequestEnum::HOTEL) {
                $reservation = new Reservation();

                $priceData = json_decode($apiBookingItem->booking_pricing_data, true);
                $totalPrice = Arr::get($priceData, 'total_price', 0);
                $totalTax = Arr::get($priceData, 'total_tax', 0);
                $totalFees = Arr::get($priceData, 'total_fees', 0);
                $subtotal = $totalPrice - ($totalTax + $totalFees);
                $advisorCommission = app(AdvisorCommissionService::class)->calculate($apiBookingItem, $subtotal);
                $priceData['advisor_commission'] = $advisorCommission;

                $reservation->date_offload = null;
                $reservation->date_travel = date('Y-m-d', strtotime($checkin));
                $reservation->passenger_surname = $passenger_surname;
                $reservation->booking_item = $filters['booking_item'];
                $reservation->booking_id = $booking_id;
                $reservation->reservation_contains = json_encode([
                    'type' => $search_type,
                    'supplier' => $supplier,
                    'booking_id' => $booking_id,
                    'booking_item' => $filters['booking_item'],
                    'search_id' => $filters['search_id'],
                    'hotel_id' => $hotelId,
                    'hotel_name' => $hotelName,
                    'hotel_images' => json_encode($hotelImages),
                    'price' => $priceData,
                ]);

                $reservation->channel_id = $channel_id;
                $reservation->total_cost = $totalCost;
                $reservation->canceled_at = null;

                $reservation->save();
            }

        } catch (Exception $e) {
            Log::error('ReservationTools | saveAddItemToReservations'.$e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }
}
