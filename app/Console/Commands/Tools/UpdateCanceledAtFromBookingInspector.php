<?php

namespace App\Console\Commands\Tools;

use App\Models\Reservation;
use App\Repositories\ApiBookingInspectorRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateCanceledAtFromBookingInspector extends Command
{
    protected $signature = 'reservations:update-canceled-at';

    protected $description = 'Update canceled_at for reservations if booking_item is canceled via ApiBookingInspectorRepository';

    public function handle()
    {
        $count = 0;
        Reservation::whereNull('canceled_at')->chunk(100, function ($reservations) use (&$count) {
            foreach ($reservations as $reservation) {
                if (! empty($reservation->booking_item) && ApiBookingInspectorRepository::isCancel($reservation->booking_item)) {
                    $reservation->canceled_at = Carbon::now();
                    $reservation->save();
                    $count++;
                }
            }
        });
        $this->info("Updated $count reservations with canceled_at.");
    }
}
