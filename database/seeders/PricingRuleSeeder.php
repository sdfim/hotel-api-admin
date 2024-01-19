<?php

namespace Database\Seeders;

use App\Models\Channel;
use App\Models\GiataProperty;
use App\Models\PricingRule;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\WithFaker;

class PricingRuleSeeder extends Seeder
{
    use WithFaker;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $priceValueTypeToApplyOptions = [
            'fixed_value',
            'percentage'
        ];

        $priceTypeToApplyOptions = [
            'total_price',
            'net_price',
            'rate_price'
        ];

        $priceValueFixedTypeToApplyOptions = [
            'per_guest',
            'per_room',
            'per_night'
        ];

        $channelId = Channel::first()->id;
        $supplierId = Supplier::first()->id;
        $giataIds = GiataProperty::where('city_id', 961)->pluck('code')->all();
        $issetIds = PricingRule::whereIn('property', $giataIds)->pluck('property')->all();
        $today = now();
        $pricingRules = [];

        foreach ($giataIds as $giataId) {
            if (in_array($giataId, $issetIds)) continue;

            $pricingRule = [
                'channel_id' => $channelId,
                'days_until_travel' => rand(1, 30),
                'destination' => 961, //New York
                'meal_plan' => $this->faker->word,
                'name' => "Rule for $giataId",
                'nights' => rand(1, 13),
                'number_rooms' => rand(1, 3),
                'price_type_to_apply' => $this->faker->randomElement($priceTypeToApplyOptions),
                'price_value_fixed_type_to_apply' => $this->faker->randomElement($priceValueFixedTypeToApplyOptions),
                'price_value_to_apply' => rand(1, 100),
                'price_value_type_to_apply' => $this->faker->randomElement($priceValueTypeToApplyOptions),
                'property' => $giataId,
                'rating' => $this->faker->randomFloat(2, 1, 5.5),
                'rate_code' => $this->faker->word,
                'room_guests' => 2,
                'room_type' => $this->faker->word,
                'rule_expiration_date' => $today->copy()->addDays(rand(30, 60))->toDateString(),
                'rule_start_date' => $today->toDateString(),
                'supplier_id' => $supplierId,
                'total_guests' => rand(1, 12),
                'total_guests_comparison_sign' => $this->faker->randomElement(['=', '<', '>']),
                'travel_date_from' => $today->copy()->addDay()->toDateString(),
                'travel_date_to' => $today->copy()->addDays(rand(3, 7))->toDateString(),
                'created_at' => $today,
                'updated_at' => $today,
            ];

            $pricingRules[] = $pricingRule;
        }

        PricingRule::insert($pricingRules);
    }
}
