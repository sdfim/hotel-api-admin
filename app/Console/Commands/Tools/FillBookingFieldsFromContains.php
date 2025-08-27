<?php

namespace App\Console\Commands\Tools;

use App\Models\Reservation;
use Illuminate\Console\Command;

class FillBookingFieldsFromContains extends Command
{
    protected $signature = 'reservations:fill-booking-fields';

    protected $description = 'Fill booking_id and booking_item from reservation_contains if empty';

    public function handle()
    {
        $count = 0;
        Reservation::chunk(100, function ($reservations) use (&$count) {
            foreach ($reservations as $reservation) {
                $data = json_decode($reservation->reservation_contains, true);
                $bookingId = $reservation->booking_id;
                $bookingItem = $reservation->booking_item;
                $newBookingId = $data['booking_id'] ?? null;
                $newBookingItem = $data['booking_item'] ?? null;

                if ((empty($bookingId) && ! empty($newBookingId)) || (empty($bookingItem) && ! empty($newBookingItem))) {
                    $reservation->booking_id = $bookingId ?: $newBookingId;
                    $reservation->booking_item = $bookingItem ?: $newBookingItem;
                    $reservation->save();
                    $count++;
                }
            }
        });
        $this->info("Updated $count reservations.");
    }
}
