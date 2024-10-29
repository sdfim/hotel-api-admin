<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Configurations\ConfigServiceType;

class ConfigServiceTypeSeeder extends Seeder
{
    public function run()
    {
        if (ConfigServiceType::count() === 0) {
            ConfigServiceType::factory()->count(5)->create();
        }
    }
}
