<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\KeyMappingOwner;

class KeyMappingOwnerFactory extends Factory
{
    protected $model = KeyMappingOwner::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
        ];
    }
}
