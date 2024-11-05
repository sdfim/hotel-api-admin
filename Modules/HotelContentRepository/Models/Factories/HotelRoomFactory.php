<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelRoom;

class HotelRoomFactory extends Factory
{
    protected $model = HotelRoom::class;

    public function definition()
    {
        return [
            'hotel_id' => Hotel::factory(),
            'name' => $this->faker->word,
            'hbsi_data_mapped_name' => $this->faker->word,
            'description' => $this->faker->paragraph,
        ];
    }
}
