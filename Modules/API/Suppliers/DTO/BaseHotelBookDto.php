<?php

namespace Modules\API\Suppliers\DTO;

use App\Models\ApiBookingItem;
use App\Models\GiataProperty;
use App\Models\MapperHbsiGiata;
use DateTime;
use Exception;
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
        $nights = (new DateTime($request['checkout']))->diff(new DateTime($request['checkin']))->days;

        $booking_item_data = json_decode($bookringItem->booking_item_data, true);
        $booking_pricing_data = json_decode($bookringItem->booking_pricing_data, true);

        if ($booking_item_data['hotel_id'] == 0 || $booking_item_data['hotel_id'] == '') {
            $booking_item_data['hotel_id'] = MapperHbsiGiata::where('hbsi_id', $booking_item_data['hotel_supplier_id'])->first()->giata_id;
        }
        $hotelName = GiataProperty::where('code', $booking_item_data['hotel_id'])->first()->name;

        $hotelBookResponseModel = new HotelBookResponseModel();
        $hotelBookResponseModel->setStatus('booked');
        $hotelBookResponseModel->setBookingId($filters['booking_id']);
        $hotelBookResponseModel->setBookringItem($filters['booking_item']);
        $hotelBookResponseModel->setSupplier($bookringItem->supplier->name);
        $hotelBookResponseModel->setHotelName($hotelName . ' (' . $booking_item_data['hotel_id'] . ')');
        $hotelBookResponseModel->setRooms([
            'room_name' => $booking_pricing_data['supplier_room_name'],
            'meal_plan' => $booking_pricing_data['meal_plan'],
        ]);
        $hotelBookResponseModel->setCancellationTerms($booking_pricing_data['cancellation_policies']);
        $hotelBookResponseModel->setRate($booking_item_data['rate_plan_code'] ?? '');
        $hotelBookResponseModel->setTotalPrice($booking_pricing_data['total_price']);
        $hotelBookResponseModel->setTotalTax($booking_pricing_data['total_tax']);
        $hotelBookResponseModel->setTotalFees($booking_pricing_data['total_fees']);
        $hotelBookResponseModel->setTotalNet($booking_pricing_data['total_net']);
        $hotelBookResponseModel->setAffiliateServiceCharge($booking_pricing_data['affiliate_service_charge']);
        $hotelBookResponseModel->setCurrency($booking_pricing_data['currency']);
        $hotelBookResponseModel->setPerNightBreakdown(round(($booking_pricing_data['total_price'] / (int)$nights), 2));

        $hotelBookResponseModel->setConfirmationNumbersList($confirmationNumbers);

        return $hotelBookResponseModel->toArray();
    }
}
