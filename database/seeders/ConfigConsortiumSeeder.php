<?php

namespace Database\Seeders;

use App\Models\Configurations\ConfigConsortium;
use Illuminate\Database\Seeder;

class ConfigConsortiumSeeder extends Seeder
{
    public function run(): void
    {
        if (ConfigConsortium::count() === 0) {
            ConfigConsortium::factory()->count(5)->create();
        }
    }
}
