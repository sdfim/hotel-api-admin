<?php

namespace App\Services;

use App\Models\ApiBookingItem;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class AdvisorCommissionService
{
    /**
     * Calculate advisor commission for a given booking item.
     *
     * - Detect supplier from booking item relation.
     * - Load search response and pick the correct supplier block.
     * - Filter commissions by date range (check-in / check-out inside range).
     * - Sum fixed commissions and percentage-based commissions
     *   (percentage is applied to the provided $totalAmount).
     */
    public function calculate(ApiBookingItem $bookingItem, float $totalAmount): float
    {
        $response = Storage::get($bookingItem->search?->client_response_path);
        $response = json_decode($response, true) ?? [];
        if (empty($response['results']) || ! is_array($response['results'])) {
            return 0.0;
        }

        // Supplier name must match the key in "results" (e.g. HBSI, HotelTrader)
        $supplierName = $bookingItem->supplier->name ?? null;
        if (! $supplierName || empty($response['results'][$supplierName])) {
            return 0.0;
        }

        $supplierResults = $response['results'][$supplierName];

        // Get hotel id from booking item data (giata_id)
        $bookingItemData = json_decode($bookingItem->booking_item_data ?? '[]', true) ?? [];
        $giataHotelId    = Arr::get($bookingItemData, 'hotel_id');

        // Get check-in / check-out from original search request
        $searchRequest = json_decode($bookingItem->search->request ?? '[]', true) ?? [];
        $checkinRaw    = Arr::get($searchRequest, 'checkin');
        $checkoutRaw   = Arr::get($searchRequest, 'checkout');

        if (! $checkinRaw || ! $checkoutRaw) {
            return 0.0;
        }

        $checkin  = Carbon::parse($checkinRaw)->startOfDay();
        $checkout = Carbon::parse($checkoutRaw)->endOfDay();

        $totalCommission = 0.0;

        foreach ($supplierResults as $hotelData) {
            // If giata_hotel_id is present, use it to select the correct hotel block
            if ($giataHotelId && Arr::get($hotelData, 'giata_hotel_id') != $giataHotelId) {
                continue;
            }

            $commissions = Arr::get($hotelData, 'commissions', []);
            if (empty($commissions)) {
                continue;
            }

            foreach ($commissions as $commission) {
                if (! $this->stayInRange($checkin, $checkout, $commission)) {
                    continue;
                }

                $value = (float) Arr::get($commission, 'commission_value', 0);
                if ($value <= 0) {
                    continue;
                }

                $type = Arr::get($commission, 'commission_value_type', 'Fixed');

                if (strcasecmp($type, 'Percentage') === 0) {
                    // Percentage from total amount (e.g. total gross price)
                    $totalCommission += $totalAmount * ($value / 100);
                } else {
                    // Treat everything else as fixed amount in booking currency
                    $totalCommission += $value;
                }
            }

            // We assume there is only one relevant hotel for this booking,
            // so we can break after processing the matched hotel.
            if ($giataHotelId) {
                break;
            }
        }

        return $totalCommission;
    }

    /**
     * Check whether the stay (check-in / check-out) is inside the commission date range.
     *
     * - date_range_start / date_range_end may be null.
     * - We consider inclusive range and treat null as "open" bound.
     */
    protected function stayInRange(Carbon $checkin, Carbon $checkout, array $commission): bool
    {
        $startRaw = Arr::get($commission, 'date_range_start');
        $endRaw   = Arr::get($commission, 'date_range_end');

        $start = $startRaw ? Carbon::parse($startRaw)->startOfDay() : null;
        $end   = $endRaw ? Carbon::parse($endRaw)->endOfDay() : null;

        if ($start && $checkin->lt($start)) {
            return false;
        }

        if ($end && $checkout->gt($end)) {
            return false;
        }

        return true;
    }
}
