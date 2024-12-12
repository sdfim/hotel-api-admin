<?php

namespace Modules\HotelContentRepository\Models\Factories;

use App\Models\Configurations\ConfigDescriptiveType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductDescriptiveContentSection;

class ProductDescriptiveContentSectionFactory extends Factory
{
    protected $model = ProductDescriptiveContentSection::class;

    public function definition()
    {
        return [
            'product_id' => Product::factory(),
            'section_name' => $this->faker->word,
            'start_date' => $this->faker->date('Y-m-d'), // Ensure valid date format
            'end_date' => $this->faker->optional()->date('Y-m-d'), // Ensure valid date format
            'descriptive_type_id' => ConfigDescriptiveType::factory(),
            'value' => $this->faker->text,
        ];
    }
}
