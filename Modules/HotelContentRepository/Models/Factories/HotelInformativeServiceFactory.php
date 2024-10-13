<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelInformativeService;

class HotelInformativeServiceFactory extends Factory
{
    protected $model = HotelInformativeService::class;

    public function definition()
    {
        return [
            'hotel_id' => Hotel::factory(),
            'service_name' => $this->faker->word,
            'service_description' => $this->faker->paragraph,
            'service_cost' => $this->faker->randomFloat(2, 10, 1000),
        ];
    }
}
