<?php

namespace Modules\Insurance\Seeders;

use Illuminate\Database\Seeder;
use Modules\HotelContentRepository\Models\Vendor;
use Modules\Insurance\Models\InsuranceProvider;

class InsuranceVendorSeeder extends Seeder
{
    public function run(): void
    {
        Vendor::firstOrCreate([
            'name' => 'TripMate',
        ], [
            'address' => 'Hazelwood, MO 63042',
            'website' => 'https://www.tripmate.com/',
            'lat' => null,
            'lng' => null,
            'verified' => true,
        ]);
    }
}
