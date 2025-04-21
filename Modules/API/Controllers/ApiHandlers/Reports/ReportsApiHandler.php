<?php

namespace Modules\API\Controllers\ApiHandlers\Reports;

use App\Models\ApiBookingInspector;
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
     * Get the missing bookings and booking errors report.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bookings(Request $request): JsonResponse
    {
        $inspectorsQuery = $this->getInspectorsQuery();
        $this->applyDateFilters($inspectorsQuery, $request);
        $inspectors = $this->transformInspectorsData($inspectorsQuery->get());

        $bookingsQuery = $this->getBookingsQuery();
        $this->applyDateFilters($bookingsQuery, $request);
        $bookings = $this->transformBookingsData($bookingsQuery->get());

        $bookingsInspectorUrl = $this->getBookingInspectorUrl();

        $data = [
            'bookings' => $bookings,
            'inspectors' => $inspectors,
            'bookings_inspector_url' => $bookingsInspectorUrl,
        ];

        return $this->sendResponse($data, 'success');
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
                'supplier',
            ]);
    }

    /**
     * Get the inspectors query.
     *
     * @return Builder
     */
    protected function getInspectorsQuery(): Builder
    {
        return ApiBookingInspector::query()
            ->where('sub_type', 'create');
    }

    /**
     * Apply date filters to the passed query.
     *
     * @param Builder $query
     * @param Request $request
     * @return void
     */
    protected function applyDateFilters(Builder $query, Request $request): void
    {
        
        $adminTimezone = 'America/New_York';

        $query->when($request->get('from'), function ($query, $from) use ($adminTimezone) {
            $fromDateUTC = Carbon::parse($from, $adminTimezone)->startOfDay()->timezone('UTC');
            $query->where('created_at', '>=', $fromDateUTC);
        });

        $query->when($request->get('to'), function ($query, $to) use ($adminTimezone) {
            $toDateUTC = Carbon::parse($to, $adminTimezone)->endOfDay()->timezone('UTC');
            $query->where('created_at', '<=', $toDateUTC);
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
        return url('admin/booking-inspector/') . '/';
    }

    /**
     * Transform the bookings meta data collection into an array for API response.
     *
     * @param Collection|ApiBookingsMetadata[] $bookings
     * @return array
     */
    protected function transformBookingsData(Collection $bookings): array
    {
        return $bookings->map(function ($booking) {
            return [
                'booking_item'              => $booking->booking_item,
                'booking_id'                => $booking->booking_id,
                'supplier'                  => $booking->supplier->name,
                'supplier_booking_item_id'  => $booking->supplier_booking_item_id,
                'hotel_supplier_id'         => $booking->hotel_supplier_id,
                'hotel'                     => $booking->hotel?->name,
                'main_guest'                => $this->getMainGuest($booking->booking_item_data),
                'created_at'                => $booking->created_at,
            ];
        })->toArray();
    }

    /**
     * Transform the booking inspectors collection into an array for API response.
     *
     * @param Collection|ApiBookingInspector[] $inspectors
     * @return array
     */
    protected function transformInspectorsData(Collection $inspectors): array
    {
        return $inspectors->map(function ($inspector) {
            return [
                'id'                        => $inspector->id,
                'booking_item'              => $inspector->booking_item,
                'booking_id'                => $inspector->booking_id,
                'status'                    => $inspector->status,
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
