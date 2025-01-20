<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\ContactInformation;
use Modules\HotelContentRepository\Models\Product;

class ContactInformationPhonesFactory extends Factory
{
    protected $model = ContactInformation::class;

    public function definition()
    {
        return [
            'contact_information_id' => ContactInformation::factory(),
            'country_code' => $this->faker->countryCode,
            'area_code' => $this->faker->areaCode,
            'phone' => $this->faker->phoneNumber,
            'extension' => $this->faker->extension,
            'description' => $this->faker->sentence,
        ];
    }
}
