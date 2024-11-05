<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Configurations\ConfigAttribute;

class ConfigAttributeSeeder extends Seeder
{
    public function run()
    {
        if (ConfigAttribute::count() === 0) {
            ConfigAttribute::factory()->count(5)->create();
        }
    }
}
