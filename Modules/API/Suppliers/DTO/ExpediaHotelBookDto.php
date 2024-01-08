<?php

namespace Modules\API\Suppliers\DTO;

use App\Models\GiataProperty;
use DateTime;
use Exception;
use Modules\API\BookingAPI\ResponseModels\HotelBookResponseModel;
use App\Models\ApiBookingItem;

class ExpediaHotelBookDto
{
    /**
     * @return array $filters
     * @throws Exception
     */
    public static function ExpediaToHotelBookResponseModel(array $filters): array
    {
        $bookringItem = ApiBookingItem::where('booking_item', $filters['booking_item'])
            ->with('supplier')
            ->with('search')
            ->first();

        $request = json_decode($bookringItem->search->request, true);
        $nights = (new DateTime($request['checkout']))->diff(new DateTime($request['checkin']))->days;

        $booking_item_data = json_decode($bookringItem->booking_item_data, true);
        $booking_pricing_data = json_decode($bookringItem->booking_pricing_data, true);

        $hotelName = GiataProperty::where('code', $booking_item_data['hotel_id'])->first()->name;

        $HotelBookResponseModel = new HotelBookResponseModel();
        $HotelBookResponseModel->setStatus('booked');
        $HotelBookResponseModel->setBookingId($filters['booking_id']);
        $HotelBookResponseModel->setBookringItem($filters['booking_item']);
        $HotelBookResponseModel->setSupplier($bookringItem->supplier->name);
        $HotelBookResponseModel->setHotelName($hotelName . ' (' . $booking_item_data['hotel_id'] . ')');
        $HotelBookResponseModel->setRooms([
            'room_name' => $booking_pricing_data['supplier_room_name'],
            'meal_plan' => '',
        ]);
        $HotelBookResponseModel->setCancellationTerms('');
        $HotelBookResponseModel->setRate($booking_item_data['rate']);
        $HotelBookResponseModel->setTotalPrice($booking_pricing_data['total_price']);
        $HotelBookResponseModel->setTotalTax($booking_pricing_data['total_tax']);
        $HotelBookResponseModel->setTotalFees($booking_pricing_data['total_fees']);
        $HotelBookResponseModel->setTotalNet($booking_pricing_data['total_net']);
        $HotelBookResponseModel->setAffiliateServiceCharge($booking_pricing_data['affiliate_service_charge']);
        $HotelBookResponseModel->setCurrency($booking_pricing_data['currency']);
        $HotelBookResponseModel->setPerNightBreakdown(round(($booking_pricing_data['total_price'] / (int)$nights), 2));

        return $HotelBookResponseModel->toArray();
    }
}
