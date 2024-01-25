<?php

namespace Modules\API\BookingAPI\Controllers;

use App\Jobs\SaveBookingInspector;
use App\Models\ApiBookingInspector;
use App\Models\ApiBookingItem;
use App\Models\ApiSearchInspector;
use App\Models\Supplier;
use Modules\Enums\SupplierNameEnum;
use Modules\Enums\TypeRequestEnum;

class HbsiBookApiController extends BaseBookApiController
{
    /**
     * @param array $filters
     * @param array $passengersData
     * @return array|null
     */
    public function addPassengers(array $filters, array $passengersData): array|null
    {
        $booking_id = $filters['booking_id'];
        $bookingItem = ApiBookingItem::where('booking_item', $filters['booking_item'])->first();
        $bookingItemData = json_decode($bookingItem->booking_item_data, true);
        $rateOccupancy = $bookingItemData['rate_occupancy'];
        $occupancy = explode('-', $rateOccupancy);
        $adults = (int)$occupancy[0];
        $children = (int)$occupancy[1] + (int)$occupancy[2];

        $filters['search_id'] = ApiBookingInspector::where('booking_item', $filters['booking_item'])->first()->search_id;

        $res = [];

        if ($bookingItem->rate_type === 'completed') {
            $bookingItemsSingle = ApiBookingInspector::where('booking_id', $booking_id)
                ->where('rate_type', 'single')
                ->where('complete_id', $filters['booking_item'])
                ->get();
            foreach ($bookingItemsSingle as $bookingItemSingle) {
                $iterFilters = $filters;
                $iterFilters['booking_item'] = $bookingItemSingle->booking_item;
                $res[] = $this->addPassengers($iterFilters, $passengersData);
            }
        }

        $bookingItemIsset = ApiBookingInspector::where('booking_id', $booking_id)
            ->where('booking_item', $filters['booking_item'])
            ->where('type', 'add_passengers');

        $apiSearchInspector = ApiSearchInspector::where('search_id', $filters['search_id'])->first()->request;
        $searchRequest = json_decode($apiSearchInspector, true);
        $countRooms = count($searchRequest['occupancy']);

        $type = ApiSearchInspector::where('search_id', $filters['search_id'])->first()->search_type;
        if ($type === TypeRequestEnum::HOTEL->value)
            for ($i = 1; $i <= $countRooms; $i++) {
                if (isset($passengersData['rooms'][$i]['passengers'])){
                    $searchAdults = $searchRequest['occupancy'][$i - 1]['adults'];
                    $searchChildren = isset($searchRequest['occupancy'][$i - 1]['children_ages'])
                        ? count($searchRequest['occupancy'][$i - 1]['children_ages'])
                        : 0;
                    if ($searchAdults === $adults && $searchChildren === $children)
                        $filters['rooms'][] = $passengersData['rooms'][$i]['passengers'];
                    $filters['rooms'][] = $passengersData['rooms'][$i]['passengers'];
                }
            }

        if ($bookingItemIsset->get()->count() > 0) {
            $bookingItemIsset->delete();
            $status = 'Passengers updated to booking.';
            $subType = 'updated';
        } else {
            $status = 'Passengers added to booking.';
            $subType = 'add';
        }

        if (empty($res)) {
            $res = [
                'booking_id' => $booking_id,
                'booking_item' => $filters['booking_item'],
                'status' => $status,
            ];

            SaveBookingInspector::dispatch([
                $booking_id,
                $filters,
                [],
                $res,
                Supplier::where('name', SupplierNameEnum::HBSI->value)->first()->id,
                'add_passengers',
                $subType,
                'hotel',
            ]);
        }

        return $res;
    }

}
