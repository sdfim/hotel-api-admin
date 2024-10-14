<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelDescriptiveContentSection;

class HotelDescriptiveContentSectionFactory extends Factory
{
    protected $model = HotelDescriptiveContentSection::class;

    public function definition()
    {
        return [
            'hotel_id' => Hotel::factory(),
            'section_name' => $this->faker->word,
            'start_date' => $this->faker->date('Y-m-d'), // Ensure valid date format
            'end_date' => $this->faker->optional()->date('Y-m-d'), // Ensure valid date format

        ];
    }
}
