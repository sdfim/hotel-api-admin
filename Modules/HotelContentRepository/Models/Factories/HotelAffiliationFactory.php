<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelAffiliation;

class HotelAffiliationFactory extends Factory
{
    protected $model = HotelAffiliation::class;

    public function definition()
    {
        return [
            'hotel_id' => Hotel::factory(),
            'affiliation_name' => $this->faker->randomElement(['UJV Exclusive Amenities', 'Consortia Inclusions']),
            'combinable' => $this->faker->boolean,
        ];
    }
}
