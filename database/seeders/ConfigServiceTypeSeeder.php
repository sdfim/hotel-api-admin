<?php

namespace Database\Seeders;

use App\Models\Configurations\ConfigServiceType;
use Illuminate\Database\Seeder;

class ConfigServiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        if (ConfigServiceType::count() === 0) {
            ConfigServiceType::factory()->count(5)->create();
        }
    }
}
