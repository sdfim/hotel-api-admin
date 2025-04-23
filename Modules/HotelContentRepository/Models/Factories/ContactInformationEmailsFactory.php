<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\ContactInformation;
use Modules\HotelContentRepository\Models\Product;

class ContactInformationEmailsFactory extends Factory
{
    protected $model = ContactInformation::class;

    public function definition()
    {
        return [
            'contact_information_id' => ContactInformation::factory(),
            'email' => $this->faker->email,
        ];
    }
}
