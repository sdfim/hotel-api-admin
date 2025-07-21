<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelRoom;

class HotelRoomFactory extends Factory
{
    protected $model = HotelRoom::class;

    public function definition(): array
    {
        return [
            'hotel_id' => Hotel::factory(),
            'name' => $this->faker->word(),
            'area' => (string) $this->faker->randomFloat(2, 0, 100),
            'external_code' => $this->faker->word(),
            'description' => $this->faker->paragraph(),
        ];
    }
}
