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
        if (! GeneralConfiguration::exists()) {
            $config = app(GeneralConfiguration::class);
            $config->currently_suppliers = ['1', '2'];
            $config->time_supplier_requests = 60;
            $config->time_reservations_kept = 7;
            $config->time_inspector_retained = 60;
            $config->star_ratings = 4;
            $config->stop_bookings = 1;
            $config->save();
        }
    }
}
