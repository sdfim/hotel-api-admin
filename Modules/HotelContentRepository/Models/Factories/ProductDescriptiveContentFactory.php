<?php

namespace Modules\HotelContentRepository\Models\Factories;

use App\Models\Configurations\ConfigDescriptiveType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\ProductDescriptiveContent;
use Modules\HotelContentRepository\Models\ProductDescriptiveContentSection;

class ProductDescriptiveContentFactory extends Factory
{
    protected $model = ProductDescriptiveContent::class;

    public function definition()
    {
        return [
            'content_sections_id' => ProductDescriptiveContentSection::factory()->create()->id,
            'descriptive_type_id' => ConfigDescriptiveType::factory()->create()->id,
            'value' => $this->faker->text,
        ];
    }
}
