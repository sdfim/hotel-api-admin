<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Configurations\ConfigDescriptiveType;

class ConfigDescriptiveTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            'Meta Description',
            'Property Description',
            'Age Restriction',
            'Cancellation Policy',
            'Pet Policy',
            'Terms and Conditions',
            'Fees Paid at Hotel'
        ];

        $locations = ['internal', 'external', 'all'];

        foreach ($types as $type) {
            $name = $type;
            if (!ConfigDescriptiveType::where('name', $name)->exists()) {
                ConfigDescriptiveType::create([
                    'name' => $name,
                    'location' => $locations[array_rand($locations)],
                    'type' => $type,
                    'description' => 'Description for ' . $name,
                ]);
            }
        }
    }
}
