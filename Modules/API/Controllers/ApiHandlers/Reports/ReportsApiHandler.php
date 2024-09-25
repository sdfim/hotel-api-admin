<?php

namespace Modules\API\Controllers\ApiHandlers\Reports;

use App\Models\ApiBookingsMetadata;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Modules\API\BaseController;

class ReportsApiHandler extends BaseController
{
    public function bookings(Request $request): JsonResponse
    {
        $bookings = ApiBookingsMetadata::query();

        if ($from = $request->get('from'))
        {
            $bookings->where('created_at', '>=', Carbon::parse($from)->startOfDay()->setTimezone('EST'));
        }

        if ($to = $request->get('to'))
        {
            $bookings->where('created_at', '<=', Carbon::parse($to)->endOfDay()->setTimezone('EST'));
        }

        $bookings = $bookings->get()->map(fn ($booking) => [
            'booking_item'              => $booking->booking_item,
            'booking_id'                => $booking->booking_id,
            'supplier'                  => $booking->supplier->name,
            'supplier_booking_item_id'  => $booking->supplier_booking_item_id,
            'hotel_supplier_id'         => $booking->hotel_supplier_id,
            'hotel'                     => $booking->hotel?->name,
            'main_guest'                => Arr::has($booking->booking_item_data, 'main_guest') ? Arr::get($booking->booking_item_data, 'main_guest.Surname').' '.Arr::get($booking->booking_item_data, 'main_guest.GivenName') : null,
            'created_at'                => $booking->created_at,
        ])->toArray();


        return $this->sendResponse($bookings, 'success');
    }
}
