<?php

namespace Modules\API\Services;

use App\Models\ApiBookingPaymentInit;
use App\Repositories\ApiBookingInspectorRepository;

class BookApiHandlerService
{
    public function addPaymentData(&$data): void
    {
        // Collect all unique booking IDs
        $bookingIds = array_unique(array_filter(array_map(fn ($item) => $item['booking_id'] ?? null, $data)));
        if (empty($bookingIds)) {
            return;
        }

        // Get all init payments for these booking IDs
        $initPayments = ApiBookingPaymentInit::whereIn('booking_id', $bookingIds)
            ->where('action', 'init')
            ->groupBy('booking_id')
            ->selectRaw('booking_id, SUM(amount) as total')
            ->pluck('total', 'booking_id');

        // Get all confirmed payments for these booking IDs
        $confirmedPayments = ApiBookingPaymentInit::whereIn('booking_id', $bookingIds)
            ->where('action', 'confirmed')
            ->groupBy('booking_id')
            ->selectRaw('booking_id, SUM(amount) as total')
            ->pluck('total', 'booking_id');

        // Get all costs for these booking IDs
        $costs = [];
        foreach ($bookingIds as $id) {
            $costs[$id] = (float) ApiBookingInspectorRepository::getPriceBookingId($id);
        }

        // Assign results to each item
        foreach ($data as &$item) {
            $bookinId = $item['booking_id'] ?? null;
            $paymentInit = $initPayments[$bookinId] ?? 0;
            $paymentConfirmed = $confirmedPayments[$bookinId] ?? 0;
            $costId = $costs[$bookinId] ?? 0;
            $item['payment'] = [
                'init' => number_format($paymentInit, 2, '.', ''),
                'confirmed' => number_format($paymentConfirmed, 2, '.', ''),
                'cost' => number_format($costId, 2, '.', ''),
            ];
        }
    }

    public static function reorderRsRetrieve(array $json): array
    {
        $orderedKeys = [
            'status', 'booking_id', 'booking_item', 'supplier', 'hotel_name', 'rooms',
            'cancellation_terms', 'rate', 'total_price', 'total_tax', 'total_fees',
            'total_net', 'markup', 'currency', 'per_night_breakdown', 'confirmation_numbers_list',
            'deposits', 'cancellation_number', 'board_basis', 'supplier_book_id',
            'billing_contact', 'billing_email', 'billing_phone', 'query',
            'deposit_information', 'hotel_image', 'hotel_address', 'booked_date', 'payment',
        ];
        $ordered = [];
        foreach ($orderedKeys as $key) {
            if (array_key_exists($key, $json)) {
                $ordered[$key] = $json[$key];
            }
        }
        // Add any remaining keys that were not in the ordered list
        foreach ($json as $key => $value) {
            if (! array_key_exists($key, $ordered)) {
                $ordered[$key] = $value;
            }
        }

        return $ordered;
    }
}
