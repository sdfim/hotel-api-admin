<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\Vendor;

class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company,
            'verified' => $this->faker->boolean,
            'address' => $this->faker->address,
            'lat' => $this->faker->latitude,
            'lng' => $this->faker->longitude,
            'website' => $this->faker->url,
        ];
    }
}
