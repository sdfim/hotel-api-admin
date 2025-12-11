<?php

namespace App\Observers;

use App\Models\ApiBookingPaymentInit;
use App\Models\Enums\PaymentStatusEnum;
use App\Models\Reservation;

class ApiBookingPaymentInitObserver
{
    public function created(ApiBookingPaymentInit $apiBookingPaymentInit)
    {
        if ($apiBookingPaymentInit->action->value === 'confirmed') {
            // Update reservation paid
            $totalAmount = ApiBookingPaymentInit::query()
                ->where('booking_id', $apiBookingPaymentInit->booking_id)
                ->where('action', PaymentStatusEnum::CONFIRMED)
                ->sum('amount');
            $reservation = Reservation::where('booking_id', $apiBookingPaymentInit->booking_id)->first();
            if ($reservation) {
                $reservation->paid = $totalAmount;
                $reservation->save();
            }
        }
    }
}
