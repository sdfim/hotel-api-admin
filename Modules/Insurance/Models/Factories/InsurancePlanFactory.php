<?php

namespace Modules\Insurance\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Insurance\Models\InsurancePlan;
use Modules\Insurance\Models\InsuranceProvider;

class InsurancePlanFactory extends Factory
{
    protected $model = InsurancePlan::class;

    public function definition(): array
    {
        $bookingItem = $this->faker->uuid;

        return [
            'booking_item' => $bookingItem,
            'total_insurance_cost' => $this->faker->randomFloat(2, 100, 10000),
            'commission_ujv' => $this->faker->randomFloat(2, 0, 1000),
            'supplier_fee' => $this->faker->randomFloat(2, 0, 1000),
            'insurance_provider_id' => InsuranceProvider::factory(), // Create a provider if needed
            //TODO: once the structure of add insurance will be done, extend object with all required fields, for niw below
            'request' => json_encode([
                'booking_item' => $bookingItem,
                'insurance_provider' => $this->faker->randomElement(['TripMate']),
            ]),
        ];
    }
}
