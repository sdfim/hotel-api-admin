<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\HotelContentRepository\DB\Seeders\HotelContentRepositorySeeder;
use Modules\Insurance\Seeders\InsuranceProviderSeeder;
use Modules\Insurance\Seeders\InsuranceRestrictionTypeSeeder;
use Modules\Insurance\Seeders\TripMateDefaultRestrictions;

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

            InsuranceProviderSeeder::class,
            InsuranceRestrictionTypeSeeder::class,
            TripMateDefaultRestrictions::class,

            HotelContentRepositorySeeder::class,
        ]);
    }
}
