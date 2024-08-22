<?php

namespace Modules\API\Suppliers\DTO;

use App\Models\ApiBookingItem;
use App\Models\GiataProperty;
use App\Models\Mapping;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Log;
use Modules\API\BookingAPI\ResponseModels\HotelBookResponseModel;

class BaseHotelBookDto
{
    /**
     * @throws Exception
     */
    public function toHotelBookResponseModel(array $filters, array $confirmationNumbers = []): array
    {
        $bookringItem = ApiBookingItem::where('booking_item', $filters['booking_item'])
            ->with('supplier')
            ->with('search')
            ->first();

        $request = json_decode($bookringItem->search->request, true);
        $nights = floor((new DateTime($request['checkout']))->diff(new DateTime($request['checkin']), true)->days);

        $booking_item_data = json_decode($bookringItem->booking_item_data, true);
        $booking_pricing_data = json_decode($bookringItem->booking_pricing_data, true);

        //        if ($booking_item_data['hotel_id'] == 0 || $booking_item_data['hotel_id'] == '') {
        //            $booking_item_data['hotel_id'] = MapperHbsiGiata::where('hbsi_id', $booking_item_data['hotel_supplier_id'])->first()->giata_id;
        //        }
        //        $hotelName = GiataProperty::where('code', $booking_item_data['hotel_id'])->first()->name;

        $hotel_id = '';
        $hotelName = '';
        if (! isset($booking_item_data['hotel_id']) || $booking_item_data['hotel_id'] == 0 || $booking_item_data['hotel_id'] == '') {
            if (isset($booking_item_data['hotel_supplier_id'])) {
                $mapper = Mapping::hBSI()->where('supplier_id', $booking_item_data['hotel_supplier_id'])->first();
                if ($mapper) {
                    $booking_item_data['hotel_id'] = $mapper->giata_id;
                } else {
                    Log::error('toHotelBookResponseModel | Unable to find MapperHbsiGiata for hotel_supplier_id: '.$booking_item_data['hotel_supplier_id']);
                }
            } else {
                Log::error('toHotelBookResponseModel | hotel_id and hotel_supplier_id are not set in booking_item_data');
            }
        }

        if (isset($booking_item_data['hotel_id'])) {
            $hotel_id = $booking_item_data['hotel_id'];
            $property = GiataProperty::where('code', $booking_item_data['hotel_id'])->first();
            if ($property) {
                $hotelName = $property->name;
            } else {
                Log::error('toHotelBookResponseModel | Unable to find GiataProperty for hotel_id: '.$booking_item_data['hotel_id']);
            }
        } else {
            Log::error('toHotelBookResponseModel | hotel_id is not set in booking_item_data');
        }

        $hotelBookResponseModel = new HotelBookResponseModel();
        $hotelBookResponseModel->setStatus('booked');
        $hotelBookResponseModel->setBookingId($filters['booking_id']);
        $hotelBookResponseModel->setBookringItem($filters['booking_item']);
        $hotelBookResponseModel->setSupplier($bookringItem->supplier->name);
        $hotelBookResponseModel->setHotelName($hotelName.' ('.$hotel_id.')');
        $hotelBookResponseModel->setRooms([
            'room_name' => $booking_pricing_data['supplier_room_name'],
            'meal_plan' => $booking_pricing_data['meal_plan'],
        ]);
        $hotelBookResponseModel->setCancellationTerms($booking_pricing_data['cancellation_policies'] ?? []);
        $hotelBookResponseModel->setRate($booking_item_data['rate_plan_code'] ?? '');
        $hotelBookResponseModel->setTotalPrice($booking_pricing_data['total_price']);
        $hotelBookResponseModel->setTotalTax($booking_pricing_data['total_tax']);
        $hotelBookResponseModel->setTotalFees($booking_pricing_data['total_fees']);
        $hotelBookResponseModel->setTotalNet($booking_pricing_data['total_net']);
        $hotelBookResponseModel->setMarkup($booking_pricing_data['markup']);
        $hotelBookResponseModel->setCurrency($booking_pricing_data['currency']);
        $hotelBookResponseModel->setPerNightBreakdown(round(($booking_pricing_data['total_price'] / (int) $nights), 2));

        $hotelBookResponseModel->setConfirmationNumbersList($confirmationNumbers);

        return $hotelBookResponseModel->toArray();
    }
}
