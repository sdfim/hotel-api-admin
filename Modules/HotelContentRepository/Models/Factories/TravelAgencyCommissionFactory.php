<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Enums\CommissionValueTypeEnum;
use Modules\HotelContentRepository\Models\Commission;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\TravelAgencyCommission;

class TravelAgencyCommissionFactory extends Factory
{
    protected $model = TravelAgencyCommission::class;

    public function definition()
    {
        $commission = Commission::create([
            'name' => $this->faker->word,
        ]);

        return [

            'product_id' => Product::factory(),
            'commission_id' => $commission->id,
            'commission_value' => $this->faker->randomFloat(2, 0, 100),
            'commission_value_type' => $this->faker->randomElement([
                CommissionValueTypeEnum::AMOUNT->value,
                CommissionValueTypeEnum::PERCENTAGE->value,
            ]),
            'date_range_start' => $this->faker->date,
            'date_range_end' => $this->faker->date,
            'room_type' => $this->faker->word,
        ];
    }
}
