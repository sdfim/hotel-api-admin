<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Configurations\ConfigAttribute;

class ConfigAttributeSeeder extends Seeder
{
    public function run()
    {
        $attributes = [
            'Board Basis',
            'Adults Only',
            'Swimming Pool',
            'Spa Facilities',
            'Gym',
            'Free WiFi',
            'Parking',
            'Pet Friendly',
            'Elevator',
            'Fitness facilities',
            'Conference space',
            'Laundry facilities',
            'Television in common areas',
            'Safe-deposit box at front desk',
            'Multilingual staff',
            '24-hour front desk',
            'Business center',
            'Express check-out',
            'Dry cleaning/laundry service',
            'Internet access - wireless',
            'Internet access in public areas - high speed',
            'Smoke-free property',
            'Wedding services',
            'Free WiFi',
            'Wheelchair accessible path of travel',
            'Number of restaurants - 1',
            '24-hour business center',
            'Number of meeting rooms - 1',
            'Snack bar/deli',
            'Free bicycles nearby',
            'Self parking (surcharge)',
            'Computer station',
            '24-hour fitness facilities'
        ];

        foreach ($attributes as $attribute) {
            ConfigAttribute::firstOrCreate(['name' => $attribute], [
                'default_value' => 'Available'
            ]);
        }
    }
}
