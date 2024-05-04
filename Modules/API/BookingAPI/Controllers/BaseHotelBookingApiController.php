<?php

namespace Modules\API\BookingAPI\Controllers;

use App\Jobs\SaveBookingInspector;
use App\Models\ApiBookingInspector;
use App\Models\ApiBookingItem;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BaseHotelBookingApiController
{
    /**
     * @param array $filters
     * @return array[]
     */
    public function removeItem(array $filters): array
    {
        $filters['search_id'] = ApiBookingInspector::where('booking_id', $filters['booking_id'])->first()->search_id;
        $booking_id = $filters['booking_id'];
        $booking_item = $filters['booking_item'];

        try {
            $bookItems = ApiBookingInspector::where('booking_id', $booking_id)
                ->where('type', 'book')
                ->where('sub_type', '!=', 'error')
                ->get()->pluck('booking_id')->toArray();

            $bookingItems = ApiBookingInspector::where('booking_item', $booking_item)
                ->where('type', 'add_item')
                ->whereNotIn('booking_id', $bookItems);

            if ($bookingItems->get()->count() === 0) {
                $res = [
                    'success' =>
                        [
                            'booking_id' => $booking_id,
                            'booking_item' => $booking_item,
                            'status' => 'This item is not in the cart',
                        ]
                ];
            } else {
                foreach ($bookingItems->get() as $item) {
                    Storage::delete($item->client_response_path);
                    Storage::delete($item->response_path);
                }

                ApiBookingInspector::where('booking_id', $booking_id)
                    ->whereIn('booking_item', $bookingItems->get()->pluck('booking_item')->toArray())
                    ->where('type', 'add_passengers')->delete();

                $bookingItems->delete();

                $res = [
                    'success' =>
                        [
                            'booking_id' => $booking_id,
                            'booking_item' => $booking_item,
                            'status' => 'Item removed from cart.',
                        ]
                ];
            }
        } catch (Exception $e) {
            $res = [
                'error' => [
                    'booking_id' => $booking_id,
                    'booking_item' => $booking_item,
                    'status' => 'Item not removed from cart.',
                ]
            ];
            Log::error('ExpediaHotelBookingApiHandler | removeItem | ' . $e->getMessage());
            Log::error($e->getTraceAsString());
        }

        $supplierId = ApiBookingItem::where('booking_item', $booking_item)->first()->supplier_id;
        SaveBookingInspector::dispatch([
            $booking_id, $filters, [], $res, $supplierId, 'remove_item', '', 'hotel',
        ]);

        return $res;
    }
}
