<?php

namespace Database\Seeders;

use App\Models\Configurations\ConfigRoomBedType;
use Illuminate\Database\Seeder;

class ConfigRoomBedTypeSeeder extends Seeder
{
    public function run(): void
    {
        $bedTypes = [
            '1 Queen Beds', '1 King Beds', '2 Queen Beds', '2 Full Beds', '1 Full Beds',
            '2 Twin Beds', '2 King Beds', '3 Queen Beds', '3 King Beds', '4 Twin Beds',
            '6 King Beds', '13 King Beds', '3 Twin Beds', '8 King Beds', '5 King Beds',
            '4 King Beds', '1 TwinXL Beds', '1 Twin Beds', '4 Queen Beds', '6 Full Beds',
            '4 Full Beds', '8 Full Beds', '5 Full Beds', '3 Full Beds', '6 Twin Beds',
            '8 Twin Beds', '12 Twin Beds', '7 King Beds', '5 Queen Beds', '2 TwinXL Beds',
            '9 King Beds', '6 Queen Beds', '10 King Beds', '8 Queen Beds', '5 Twin Beds',
            '4 TwinXL Beds', '20 King Beds',
        ];

        foreach ($bedTypes as $bedType) {
            ConfigRoomBedType::firstOrCreate(['name' => $bedType]);
        }
    }
}
