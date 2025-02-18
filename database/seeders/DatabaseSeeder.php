<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\HotelContentRepository\DB\Seeders\HotelContentRepositorySeeder;
use Modules\Insurance\Seeders\InsuranceRateTierSeeder;
use Modules\Insurance\Seeders\InsuranceTypeSeeder;
use Modules\Insurance\Seeders\InsuranceVendorSeeder;
use Modules\Insurance\Seeders\InsuranceRestrictionTypeSeeder;
use Modules\Insurance\Seeders\TripMateDefaultRestrictionsSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            UserSeeder::class,
            SuppliersSeeder::class,
            ChannelSeeder::class,
            GeneralConfigurationSeeder::class,

            InsuranceVendorSeeder::class,
            InsuranceRestrictionTypeSeeder::class,
            InsuranceTypeSeeder::class,
            TripMateDefaultRestrictionsSeeder::class,
            InsuranceRateTierSeeder::class,

            HotelContentRepositorySeeder::class,

            ConfigConsortiumSeeder::class,
            ConfigAttributeSeeder::class,
            ConfigServiceTypeSeeder::class,
            ConfigDescriptiveTypeSeeder::class,
        ]);
    }
}
