<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GeneralConfiguration;
use App\Models\Reservation;

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

		# delete by day config (time_Reservation_kept)
		$kept_days = GeneralConfiguration::first()->time_Reservation_kept;
		$kept_date = date('Y-m-d H:i:s', strtotime('-' . $kept_days . ' days'));
		Reservation::where('date_travel', '<', $kept_date)->delete();

		# if is Offload Date delete by offload date three_months
		$three_months = date('Y-m-d H:i:s', strtotime('-3 months'));
		Reservation::where('date_offload', '<', $three_months)->delete();
    }
}
