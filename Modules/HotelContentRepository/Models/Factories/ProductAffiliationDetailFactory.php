<?php

namespace Modules\HotelContentRepository\Models\Factories;

use App\Models\Configurations\ConfigConsortium;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\ProductAffiliationDetail;

class ProductAffiliationDetailFactory extends Factory
{
    protected $model = ProductAffiliationDetail::class;

    public function definition()
    {
        return [
            'affiliation_id' => ProductAffiliation::factory(),
            'consortia_id' => ConfigConsortium::factory(),
            'description' => $this->faker->sentence,
            'start_date' => $this->faker->date,
            'end_date' => $this->faker->date,
        ];
    }
}
