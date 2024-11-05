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
            'Cancellation Policy',
            'Pet Policy',
            'Terms and Conditions',
            'Fees Paid at Hotel'
        ];

        $locations = ['internal', 'external', 'all'];

        foreach ($types as $type) {
            foreach ($locations as $location) {
                $name = $type . ' ' . $location;
                if (!ConfigDescriptiveType::where('name', $name)->exists()) {
                    ConfigDescriptiveType::create([
                        'name' => $name,
                        'location' => $location,
                        'type' => $type,
                        'description' => 'Description for ' . $name,
                    ]);
                }
            }
        }
    }
}
