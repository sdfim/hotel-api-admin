<?php

namespace Database\Seeders;

use App\Models\Channel;
use App\Models\GiataProperty;
use App\Models\PricingRule;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class PricingRuleSeeder extends Seeder
{
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
        $giataIds = GiataProperty::where('city', 'New York')->pluck('code')->all();
        $issetIds = PricingRule::whereIn('property', $giataIds)->pluck('property')->all();
        $today = now();
        $data = [];

        foreach ($giataIds as $giataId) {
            if (in_array($giataId, $issetIds)) continue;

            $days = rand(1, 14);
            $nights = $days > 1 ? $days - 1 : 1;

            $pricingRule = [
                'name' => "Rule for $giataId",
                'property' => $giataId,
                'destination' => 'New York',
                'travel_date' => $today,
                'supplier_id' => $supplierId,
                'channel_id' => $channelId,
                'days' => $days,
                'nights' => $nights,
                'rate_code' => rand(1000, 10000),
                'room_type' => 'test type',
                'meal_plan' => 'test meal plan',
                'rating' => $this->randFloat(4.0, 5.5),
                'price_value_to_apply' => rand(1, 100),
                'rule_start_date' => $today,
                'rule_expiration_date' => $today->copy()->addDays(rand(30, 60)),
                'created_at' => $today,
                'updated_at' => $today,
            ];

            $pricingRule['number_rooms'] = rand(1, 4);
            $pricingRule['room_guests'] = $pricingRule['number_rooms'] > 1 ? $pricingRule['number_rooms'] - 1 : 0;
            $pricingRule['total_guests'] = rand($pricingRule['number_rooms'], $pricingRule['number_rooms'] * 2);
            $pricingRule['price_value_type_to_apply'] = $priceValueTypeToApplyOptions[rand(0, 1)];
            $pricingRule['price_type_to_apply'] = $priceTypeToApplyOptions[rand(0, 2)];
            if ($pricingRule['price_value_type_to_apply'] === 'fixed_value') {
                $pricingRule['price_value_fixed_type_to_apply'] = $priceValueFixedTypeToApplyOptions[rand(0, 2)];
            } else {
                $pricingRule['price_value_fixed_type_to_apply'] = null;
            }

            $data[] = $pricingRule;
        }

        PricingRule::insert($data);
    }

    /**
     * @param float $minValue
     * @param float $maxValue
     * @return float
     */
    private function randFloat(float $minValue, float $maxValue): float
    {
        return round($minValue + mt_rand() / mt_getrandmax() * ($maxValue - $minValue), 2);
    }
}
