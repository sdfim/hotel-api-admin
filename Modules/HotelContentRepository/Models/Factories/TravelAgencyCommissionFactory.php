<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Enums\CommissionValueTypeEnum;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\TravelAgencyCommission;

class TravelAgencyCommissionFactory extends Factory
{
    protected $model = TravelAgencyCommission::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'commission_value' => $this->faker->randomFloat(2, 0, 100),
            'commission_value_type' => $this->faker->randomElement([
                CommissionValueTypeEnum::AMOUNT->value,
                CommissionValueTypeEnum::PERCENTAGE->value,
            ]),
            'date_range_start' => $this->faker->date,
            'date_range_end' => $this->faker->date,
        ];
    }
}
