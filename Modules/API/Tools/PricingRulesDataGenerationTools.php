<?php

namespace Modules\API\Tools;

use App\Models\Channel;
use App\Models\Supplier;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\Carbon;

class PricingRulesDataGenerationTools
{
    public Generator $faker;

    public Carbon $today;

    public function __construct()
    {
        $this->faker = Factory::create();
        $this->today = Carbon::now();
    }

    /**
     * @return string[]
     */
    public function getManipulablePriceTypeKeys(): array
    {
        return ['total_price', 'net_price'];
    }

    /**
     * @return string[]
     */
    public function getPriceValueTypeKeys(): array
    {
        return ['fixed_value', 'percentage'];
    }

    /**
     * @return string[]
     */
    public function getPriceValueTargetKeys(): array
    {
        return ['per_guest', 'per_room', 'per_night', 'not_applicable'];
    }

    /**
     * @return string[]
     */
    public function getPricingRuleConditionFields(): array
    {
        return [
            'supplier_id',
            'channel_id',
            'property',
            'destination',
            'travel_date',
            'booking_date',
            'total_guests',
            'days_until_departure',
            'nights',
            'rating',
            'number_of_rooms',
            'rate_code',
            'room_type',
            'meal_plan',
        ];
    }

    public function generatePricingRuleWithConditionsData($ruleName): array
    {
        $pricingRule = $this->generatePricingRuleData($ruleName);

        $pricingRuleConditions = $this->generatePricingRuleConditionsData();

        $pricingRule['conditions'] = $pricingRuleConditions;

        return $pricingRule;
    }

    public function generatePricingRuleData($name): array
    {
        $priceValueType = $this->faker->randomElement($this->getPriceValueTypeKeys());

        return [
            'name' => "Pricing rule $name",
            'rule_start_date' => $this->today->copy()->toDateString(),
            'rule_expiration_date' => $this->today->copy()->addDays(rand(30, 60))->toDateString(),
            'manipulable_price_type' => $this->faker->randomElement($this->getManipulablePriceTypeKeys()),
            'price_value' => $priceValueType === 'percentage' ? rand(1, 6) : rand(1, 100),
            'price_value_type' => $priceValueType,
            'price_value_target' => $this->faker->randomElement($this->getPriceValueTargetKeys()),
        ];
    }

    public function generatePricingRuleConditionsData(?int $giataId = null): array
    {
        $pricingRuleConditionsData = [];

        $randPricingRuleConditionFields = $this->faker->randomElements($this->getPricingRuleConditionFields(), rand(1, 14));

        foreach ($randPricingRuleConditionFields as $field) {
            $pricingRuleConditionsData[] = $this->pricingRuleConditionApplyLogic($field, $giataId);
        }

        return $pricingRuleConditionsData;
    }

    public function generatePricingRuleConditionData(): array
    {
        $field = $this->faker->randomElement($this->getPricingRuleConditionFields());

        return $this->pricingRuleConditionApplyLogic($field);
    }

    protected function pricingRuleConditionApplyLogic(string $field, ?int $giataId = null): array
    {
        $channelIds = Channel::pluck('id')->toArray();

        $supplierIds = Supplier::pluck('id')->toArray();

        $giataIds = [10000011, 10000044, 10000066, 10000171, 10000215, 10000273, 10000320, 10000353, 10000433, 10000560];

        $compare = match ($field) {
            'supplier_id', 'channel_id', 'property', 'destination', 'rate_code', 'room_type', 'meal_plan' => '=',
            default => $this->faker->randomElement(['=', '<', '>', 'between'])
        };

        $condition = [
            'field' => $field,
            'compare' => $compare,
        ];

        if (in_array($field, ['supplier_id', 'channel_id', 'property', 'destination', 'rate_code', 'room_type', 'meal_plan'])) {
            $condition['value_from'] = match ($field) {
                'supplier_id' => $this->faker->randomElement($supplierIds) ?? 1,
                'channel_id' => $this->faker->randomElement($channelIds) ?? 1,
                'property' => $giataId ?? $this->faker->randomElement($giataIds),
                'destination' => 961, //New York
                'rate_code', 'room_type', 'meal_plan' => $this->faker->word
            };
        } elseif (in_array($field, ['travel_date', 'booking_date'])) {
            $condition['value_from'] = $this->today->copy()->addDay()->toDateString();

            $condition['value_to'] = match ($compare) {
                '=', '<', '>' => null,
                'between' => $this->today->copy()->addDays(rand(2, 7))->toDateString()
            };
        } elseif ($field === 'total_guests') {
            $condition['value_from'] = rand(3, 4);

            $condition['value_to'] = match ($compare) {
                '=', '<', '>' => null,
                'between' => rand(5, 8)
            };
        } elseif ($field === 'days_until_departure') {
            $condition['value_from'] = rand(1, 8);

            $condition['value_to'] = match ($compare) {
                '=', '<', '>' => null,
                'between' => rand(9, 16)
            };
        } elseif ($field === 'nights') {
            $condition['value_from'] = rand(1, 6);

            $condition['value_to'] = match ($compare) {
                '=', '<', '>' => null,
                'between' => rand(7, 14)
            };
        } elseif ($field === 'rating') {
            $condition['value_from'] = $this->faker->randomFloat(2, 1.0, 3.0);

            $condition['value_to'] = match ($compare) {
                '=', '<', '>' => null,
                'between' => $this->faker->randomFloat(2, 3.1, 5.5)
            };
        } else {
            // 'number_of_rooms'
            $condition['value_from'] = 1;

            $condition['value_to'] = match ($compare) {
                '=', '<', '>' => null,
                'between' => rand(2, 3)
            };
        }

        return $condition;
    }
}
