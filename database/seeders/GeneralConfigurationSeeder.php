<?php

namespace Database\Seeders;

use App\Models\GeneralConfiguration;
use Illuminate\Database\Seeder;

class GeneralConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $config = new GeneralConfiguration();
        $config->currently_suppliers = ['1'];
        $config->time_supplier_requests = 3;
        $config->time_reservations_kept = 7;
        $config->time_inspector_retained = 60;
        $config->star_ratings = 4;
        $config->stop_bookings = 1;
        $config->save();
    }
}
