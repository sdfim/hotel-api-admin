<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Configurations\ConfigConsortium;

class ConfigConsortiumSeeder extends Seeder
{
    public function run()
    {
        if (ConfigConsortium::count() === 0) {
            ConfigConsortium::factory()->count(5)->create();
        }
    }
}
