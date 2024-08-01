<?php

namespace Modules\API\BookingAPI\Controllers;

use App\Jobs\SaveBookingInspector;
use App\Models\ApiBookingInspector;
use App\Models\ApiBookingItem;
use App\Repositories\ApiBookingInspectorRepository;
use App\Repositories\ApiBookingsMetadataRepository;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BaseHotelBookingApiController
{
    /**
     * @return array[]
     */
    public function removeItem(array $filters): array
    {
        $filters['search_id'] = ApiBookingInspector::where('booking_id', $filters['booking_id'])->first()->search_id;
        $booking_id = $filters['booking_id'];
        $booking_item = $filters['booking_item'];

        $supplierId = ApiBookingItem::where('booking_item', $booking_item)->first()->supplier_id;
        $bookingInspector = ApiBookingInspectorRepository::newBookingInspector([
            $booking_id, $filters, $supplierId, 'remove_item', '', 'hotel',
        ]);

        try {
            // Check by ApiBookingsMetadata
            $itemsBooked = ApiBookingsMetadataRepository::bookedItem($booking_id, $booking_item);

            // Check by ApiBookingInspector
            $bookItems = ApiBookingInspector::where('booking_id', $booking_id)
                ->where('type', 'book')
                ->where('status', '!=', 'error')
                ->get()->pluck('booking_item')->toArray();

            $bookingItems = ApiBookingInspector::where('booking_item', $booking_item)
                ->where('type', 'add_item')
                ->whereNotIn('booking_item', $bookItems);

            $errorMessage = null;

            if ($itemsBooked->first()?->supplier_booking_item_id) {
                $errorMessage = 'This item is already booked. Supplier booking confirmation code '.$itemsBooked->first()?->supplier_booking_item_id;
                $res = [
                    'error' => [
                        'booking_id' => $booking_id,
                        'booking_item' => $booking_item,
                        'status' => $errorMessage,
                    ],
                ];
            } elseif ($bookingItems->get()->count() === 0) {
                $errorMessage = 'This item is not in the cart';
                $res = [
                    'error' => [
                        'booking_id' => $booking_id,
                        'booking_item' => $booking_item,
                        'status' => $errorMessage,
                    ],
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
                    'success' => [
                        'booking_id' => $booking_id,
                        'booking_item' => $booking_item,
                        'status' => 'Item removed from cart.',
                    ],
                ];
            }

            if ($errorMessage) {
                SaveBookingInspector::dispatch($bookingInspector, [], [], 'error', ['side' => 'app', 'message' => $errorMessage]);
            } else {
                SaveBookingInspector::dispatch($bookingInspector, [], $res);
            }

        } catch (Exception $e) {
            $res = [
                'error' => [
                    'booking_id' => $booking_id,
                    'booking_item' => $booking_item,
                    'status' => 'Item not removed from cart.',
                ],
            ];
            Log::error('ExpediaHotelBookingApiHandler | removeItem | '.$e->getMessage());
            Log::error($e->getTraceAsString());

            SaveBookingInspector::dispatch($bookingInspector, [], $res, 'error', ['side' => 'app', 'message' => $e->getMessage()]);
        }

        return $res;
    }
}
