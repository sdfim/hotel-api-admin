<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\HotelDescriptiveContent;
use Modules\HotelContentRepository\Models\HotelDescriptiveContentSection;

class HotelDescriptiveContentFactory extends Factory
{
    protected $model = HotelDescriptiveContent::class;

    public function definition()
    {
        return [
            'content_sections_id' => HotelDescriptiveContentSection::factory()->create()->id,
            'section_name' => $this->faker->word,
            'meta_description' => $this->faker->sentence,
            'property_description' => $this->faker->paragraph,
            'cancellation_policy' => $this->faker->paragraph,
            'pet_policy' => $this->faker->paragraph,
            'terms_conditions' => $this->faker->paragraph,
            'fees_paid_at_hotel' => $this->faker->paragraph,
            'staff_contact_info' => $this->faker->paragraph,
            'validity_start' => $this->faker->date,
            'validity_end' => $this->faker->optional()->date,
        ];
    }
}
