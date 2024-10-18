<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\HotelContentRepository\DB\Seeders\HotelContentRepositorySeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            UserSeeder::class,
            SuppliersSeeder::class,
            ChannelSeeder::class,
            GeneralConfigurationSeeder::class,
//            PropertyWeightingSeeder::class,
//            PricingRuleSeeder::class,

            HotelContentRepositorySeeder::class,
        ]);
    }
}
