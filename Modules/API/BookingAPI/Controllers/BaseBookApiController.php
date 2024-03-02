<?php

namespace Modules\API\BookingAPI\Controllers;

use App\Models\ApiBookingInspector;
use App\Models\ApiBookingItem;
use App\Models\ApiSearchInspector;
use App\Models\Supplier;
use App\Repositories\ApiBookingInspectorRepository as BookingRepository;
use Modules\API\BaseController;

class BaseBookApiController extends BaseController
{
    /**
     * @param ApiBookingInspector $bookingInspector
     * @return array|null
     */
    public function retrieveItem(ApiBookingInspector $bookingInspector): array|null
    {
        $apiBookingItem = ApiBookingItem::where('booking_item', $bookingInspector->booking_item)->first();
        $booking_item_data = json_decode($apiBookingItem->booking_item_data, true);
        $booking_pricing_data = json_decode($apiBookingItem->booking_pricing_data, true);

        $searchInspector = ApiSearchInspector::where('search_id', $bookingInspector->search_id)->first();

        $passengers = BookingRepository::getPassengers($bookingInspector->booking_id, $bookingInspector->booking_item);
        $dataPassengers = [];
        if ($passengers) {
            $passengersArr = $passengers->toArray();
            $dataPassengers = json_decode($passengersArr['request'], true);
        }

        $supplier_id = $apiBookingItem->supplier_id;
        $supplier = Supplier::find($supplier_id)->name;

        return [
            'booking_id' => $bookingInspector->booking_id,
            'booking_item' => $bookingInspector->booking_item,
            'search_id' => $bookingInspector->search_id,
            'supplier' => $supplier,
            'supplier_data' => $booking_item_data,
            'pricing_data' => $booking_pricing_data,
            'passengers' => $dataPassengers,
            'request' => json_decode($searchInspector->request, true),
        ];
    }

    /**
     * @param string $booking_id
     * @param string $booking_item
     * @return array[]
     */
    public function tailBookResponse(string $booking_id, string $booking_item): array
    {
        return [
            'links' => [
                'remove' => [
                    'method' => 'DELETE',
                    'href' => '/api/booking/cancel-booking?booking_id=' . $booking_id . '&booking_item=' . $booking_item,
                ],
                'change' => [
                    'method' => 'PUT',
                    'href' => '/api/booking/change-booking?booking_id=' . $booking_id . '&booking_item=' . $booking_item,
                ],
                'retrieve' => [
                    'method' => 'GET',
                    'href' => '/api/booking/retrieve-booking?booking_id=' . $booking_id,
                ],
            ],
        ];
    }
}
