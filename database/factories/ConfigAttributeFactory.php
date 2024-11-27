<?php

namespace Database\Factories;

use App\Models\Configurations\ConfigAttribute;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConfigAttributeFactory extends Factory
{
    protected $model = ConfigAttribute::class;

    public function definition()
    {
        return [
            'name' => $this->faker->randomElement([
                'Board Basis',
                'Adults Only',
                'Swimming Pool',
                'Spa Facilities',
                'Gym',
                'Free WiFi',
                'Parking',
                'Pet Friendly'
            ]),
            'default_value' => $this->faker->randomElement([
                'All Inclusive',
                'Yes',
                'No',
                'Available',
                'Not Available'
            ]),
        ];
    }
}
