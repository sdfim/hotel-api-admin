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
}
