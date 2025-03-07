<?php

namespace Modules\API\Controllers\ApiHandlers\Reports;

use App\Models\ApiBookingsMetadata;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Modules\API\BaseController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReportsApiHandler extends BaseController
{
    /**
     * Get the bookings report.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bookings(Request $request): JsonResponse
    {
        $bookingsQuery = $this->getBookingsQuery();
        $this->applyDateFilters($bookingsQuery, $request);

        $bookingsInspectorUrl = $this->getBookingInspectorUrl();
        $bookings = $this->transformBookingsData($bookingsQuery->get(), $bookingsInspectorUrl);

        return $this->sendResponse($bookings, 'success');
    }

    /**
     * Get the booking query.
     *
     * @return Builder
     */
    protected function getBookingsQuery(): Builder
    {
        return ApiBookingsMetadata::query()
            ->with([
                'inspectors' => function ($query) {
                    $query->where('sub_type', 'create');
                },
                'supplier',
            ]);
    }

    /**
     * Apply date filters to the booking query.
     *
     * @param Builder $query
     * @param Request $request
     * @return void
     */
    protected function applyDateFilters(Builder $query, Request $request): void
    {
        $query->when($request->get('from'), function ($query, $from) {
            $query->where('created_at', '>=', Carbon::parse($from)->startOfDay()->setTimezone('EST'));
        });

        $query->when($request->get('to'), function ($query, $to) {
            $query->where('created_at', '<=', Carbon::parse($to)->endOfDay()->setTimezone('EST'));
        });
    }

    /**
     * Get the booking inspector URL.
     *
     * @todo Improve this by adding the route in the API module provider.
     * @return string
     */
    protected function getBookingInspectorUrl(): string
    {
        return config('app.app_url') . '/admin/booking-inspector/';
    }

        /**
     * Transform the bookings collection into an array for API response.
     *
     * @param Collection|ApiBookingsMetadata[] $bookings
     * @param string $bookingsInspectorUrl
     * @return array
     */
    protected function transformBookingsData(Collection $bookings, string $bookingsInspectorUrl): array
    {
        return $bookings->map(function ($booking) use ($bookingsInspectorUrl) {
            return [
                'booking_item'              => $booking->booking_item,
                'booking_id'                => $booking->booking_id,
                'booking_id_url'            => $bookingsInspectorUrl,
                'supplier'                  => $booking->supplier->name,
                'supplier_booking_item_id'  => $booking->supplier_booking_item_id,
                'hotel_supplier_id'         => $booking->hotel_supplier_id,
                'hotel'                     => $booking->hotel?->name,
                'main_guest'                => $this->getMainGuest($booking->booking_item_data),
                'created_at'                => $booking->created_at,
                'booking_attempts'         => $booking->inspectors,
            ];
        })->toArray();
    }

    /**
     * Get the main guest name from the booking item data.
     *
     * @param array $bookingItemData
     * @return string|null
     */
    protected function getMainGuest($bookingItemData): ?string
    {
        return Arr::has($bookingItemData, 'main_guest')
            ? Arr::get($bookingItemData, 'main_guest.Surname').' '.Arr::get($bookingItemData, 'main_guest.GivenName')
            : null;
    }
}
