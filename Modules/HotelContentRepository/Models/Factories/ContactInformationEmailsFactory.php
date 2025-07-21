<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\ContactInformation;

class ContactInformationEmailsFactory extends Factory
{
    protected $model = ContactInformation::class;

    public function definition(): array
    {
        return [
            'contact_information_id' => ContactInformation::factory(),
            'email' => $this->faker->email(),
        ];
    }
}
