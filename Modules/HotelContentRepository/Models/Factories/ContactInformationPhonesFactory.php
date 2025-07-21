<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\ContactInformation;

class ContactInformationPhonesFactory extends Factory
{
    protected $model = ContactInformation::class;

    public function definition(): array
    {
        return [
            'contact_information_id' => ContactInformation::factory(),
            'country_code' => $this->faker->countryCode(),
            'area_code' => $this->faker->areaCode,
            'phone' => $this->faker->phoneNumber(),
            'extension' => $this->faker->extension,
            'description' => $this->faker->sentence(),
        ];
    }
}
