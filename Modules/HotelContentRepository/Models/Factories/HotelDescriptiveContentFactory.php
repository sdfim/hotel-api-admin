<?php

namespace Modules\HotelContentRepository\Models\Factories;

use App\Models\Configurations\ConfigDescriptiveType;
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
            'descriptive_type_id' => ConfigDescriptiveType::factory()->create()->id,
            'value' => $this->faker->text,
        ];
    }
}
