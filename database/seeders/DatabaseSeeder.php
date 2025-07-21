<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\HotelContentRepository\DB\Seeders\HotelContentRepositorySeeder;

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
            ConfigRoomBedTypeSeeder::class,
            ConfigContactInformationDepartmentSeeder::class,

            HotelContentRepositorySeeder::class,

            ConfigInsuranceDocumentationTypeSeeder::class,
            ConfigConsortiumSeeder::class,
            ConfigAttributeSeeder::class,
            ConfigServiceTypeSeeder::class,
            ConfigDescriptiveTypeSeeder::class,
        ]);
    }
}
