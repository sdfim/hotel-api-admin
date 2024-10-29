<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\HotelAgeRestriction;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelAgeRestrictionType;

class HotelAgeRestrictionFactory extends Factory
{
    protected $model = HotelAgeRestriction::class;

    public function definition()
    {
        return [
            'hotel_id' => Hotel::factory(),
            'restriction_type_id' => HotelAgeRestrictionType::factory(),
            'value' => $this->faker->numberBetween(1, 18),
            'active' => $this->faker->boolean,
        ];
    }
}
