<?php

namespace Database\Seeders;

use App\Models\Configurations\ConfigDescriptiveType;
use Illuminate\Database\Seeder;

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
        ];

        $locations = ['internal', 'external', 'all'];

        foreach ($types as $type) {
            $name = $type;
            if (! ConfigDescriptiveType::where('name', $name)->exists()) {
                ConfigDescriptiveType::create([
                    'name' => $name,
                    'location' => $locations[array_rand($locations)],
                    'type' => $type,
                    'description' => 'Description for '.$name,
                ]);
            }
        }

        if (! ConfigDescriptiveType::where('type', 'Taxes And Fees')->exists()) {
            ConfigDescriptiveType::create([
                'name' => 'know_before_you_go',
                'location' => 'all',
                'type' => 'Taxes And Fees',
                'description' => 'Taxes And Fees type will additionally be included in the Hotel Fees section of the Content API.',
            ]);
        }
    }
}
