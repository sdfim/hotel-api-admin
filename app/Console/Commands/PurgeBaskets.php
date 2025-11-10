<?php

namespace App\Console\Commands;

use App\Models\ApiBookingInspector;
use App\Models\GeneralConfiguration;
use App\Models\Reservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeBaskets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purge-baskets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'A background routine to purge any baskets that have not turned into a booking';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        /*
        A background routine to purge any baskets that have not turned into a booking.
        If they have turned into a booking they will be kept until 3 months after the date of travel.
        All other baskets will be purged based on creation data and a configuration variable
        that will be set within the Administration Suite.
        */

        /*
        // delete by day config (time_Reservation_kept)
        $this->info('PurgeBaskets: delete by day config (time_Reservation_kept)');
        $kept_days = GeneralConfiguration::first()->time_reservations_kept;
        $kept_date = date('Y-m-d H:i:s', strtotime('-'.$kept_days.' days'));
        $reservation = Reservation::where('date_travel', '<', $kept_date);
        if ($reservation->count() > 0) {
            $reservations = $reservation->get();
            foreach ($reservations as $res) {
                $res->delete();
            }
        }

        // if is Offload Date delete by offload date three_months
        $this->info('PurgeBaskets: if is Offload Date delete by offload date three_months');
        $three_months = date('Y-m-d H:i:s', strtotime('-3 months'));
        $reservation = Reservation::where('date_offload', '<', $three_months);
        if ($reservation->count() > 0) {
            $reservations = $reservation->get();
            foreach ($reservations as $res) {
                $res->delete();
            }
        }
        */

        // Stop bookings with in a number of hours from time of search execution, hours*
        $deleteBookingItems = [];
        $this->info('PurgeBaskets: Stop bookings with in a number of hours from time of search execution, hours*');
        $kept_hours = GeneralConfiguration::first()->stop_bookings;
        // Is NOT Book Status
        $booking = ApiBookingInspector::with('search')
            ->whereNotIn('booking_id', function ($subQuery) {
                $subQuery->select('booking_id')
                    ->from('api_booking_inspector')
                    ->where('type', 'book')
                    ->where('sub_type', 'create')
                    ->where('status', 'success')
                    ->distinct();
            })
            ->get();
        foreach ($booking as $b) {
            $diff = strtotime($b->search->created_at) - strtotime(date('Y-m-d H:i:s'));
            $hours = $diff / 3600;
            if ($hours < $kept_hours) {
                $deleteBookingItems[] = $b->booking_id;
                Storage::delete($b->response_path);
                Storage::delete($b->client_response_path);
            }
        }
        ApiBookingInspector::whereIn('booking_id', $deleteBookingItems)->delete();
    }
}
