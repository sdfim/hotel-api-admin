<?php

namespace Modules\API\BookingAPI\Controllers;

use App\Jobs\SaveBookingInspector;
use App\Models\ApiBookingInspector;
use App\Models\ApiBookingItem;
use App\Models\ApiSearchInspector;
use App\Models\Supplier;
use App\Repositories\ApiBookingInspectorRepository as BookingRepository;
use Modules\API\BaseController;
use Modules\Enums\TypeRequestEnum;

class BaseBookApiController extends BaseController
{
    public function retrieveItem(ApiBookingInspector $bookingInspector): ?array
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
     * @return array[]
     */
    public function tailBookResponse(string $booking_id, string $booking_item): array
    {
        return [
            'links' => [
                'remove' => [
                    'method' => 'DELETE',
                    'href' => '/api/booking/cancel-booking?booking_id='.$booking_id.'&booking_item='.$booking_item,
                ],
                'change' => [
                    'method' => 'PUT',
                    'href' => '/api/booking/change-booking?booking_id='.$booking_id.'&booking_item='.$booking_item,
                ],
                'retrieve' => [
                    'method' => 'GET',
                    'href' => '/api/booking/retrieve-booking?booking_id='.$booking_id,
                ],
            ],
        ];
    }

    public function addPassengers(array $filters, array $passengersData, string $supplierName): ?array
    {
        $booking_id = $filters['booking_id'];
        $filters['search_id'] = ApiBookingInspector::where('booking_item', $filters['booking_item'])->first()->search_id;

        $bookingItem = ApiBookingInspector::where('booking_id', $booking_id)
            ->where('booking_item', $filters['booking_item'])
            ->where('type', 'add_passengers');

        $apiSearchInspector = ApiSearchInspector::where('search_id', $filters['search_id'])->first()->request;

        $countRooms = count(json_decode($apiSearchInspector, true)['occupancy']);

        $type = ApiSearchInspector::where('search_id', $filters['search_id'])->first()->search_type;
        if (TypeRequestEnum::from($type) === TypeRequestEnum::HOTEL) {
            for ($i = 1; $i <= $countRooms; $i++) {
                if (array_key_exists($i, $passengersData['rooms'])) {
                    $filters['rooms'][] = $passengersData['rooms'][$i]['passengers'];
                }
            }
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
            'booking_id' => $booking_id,
            'booking_item' => $filters['booking_item'],
            'status' => $status,
        ];

        $supplierId = Supplier::where('name', $supplierName)->first()->id;
        $bookingInspector = BookingRepository::newBookingInspector([
            $booking_id, $filters, $supplierId, 'add_passengers', $subType, 'hotel',
        ]);
        SaveBookingInspector::dispatch($bookingInspector, [], $res);

        return $res;
    }
}
