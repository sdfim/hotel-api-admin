<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\ContactInformation;
use Modules\HotelContentRepository\Models\Product;

class ContactInformationFactory extends Factory
{
    protected $model = ContactInformation::class;

    public function definition(): array
    {
        return [
            'contactable_id' => Product::factory(),
            'contactable_type' => Product::class,
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'job_title' => $this->faker->jobTitle(),
        ];
    }
}
