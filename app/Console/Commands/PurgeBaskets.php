<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GeneralConfiguration;
use App\Models\Reservations;

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
    public function handle()
    {
		/*
		A background routine to purge any baskets that have not turned into a booking. 
		If they have turned into a booking they will be kept until 3 months after the date of travel. 
		All other baskets will be purged based on creation data and a configuration variable 
		that will be set within the Administration Suite.
		*/		

		# delete by date_travel > 3 months
		// TODO: check STATUS is BOOKED
		$three_months = date('Y-m-d H:i:s', strtotime('-3 months'));
		$reservations = Reservations::where('date_travel', '<', $three_months)
			// ->where('status', '=', 'BOOKED')
			->get();
		foreach ($reservations as $reservation) {
			$reservation->delete();
		}

		# delete by day config (time_reservations_kept)
		$kept_days = GeneralConfiguration::first()->time_reservations_kept;
		$kept_date = date('Y-m-d H:i:s', strtotime('-' . $kept_days . ' days'));
		$reservations = Reservations::where('date_travel', '<', $kept_date)->get();
		foreach ($reservations as $reservation) {
			$reservation->delete();
		}

		# if is Offload Date delete by offload date | Offload Date - дата снятия резерва
		$offload_date = date('Y-m-d H:i:s');
		$reservations = Reservations::where('date_offload', '<', $offload_date)->get();
		foreach ($reservations as $reservation) {
			$reservation->delete();
		}
    }
}
