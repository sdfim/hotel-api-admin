<?php

namespace Modules\API\Tools;

use App\Models\Channel;
use App\Models\PricingRule;
use App\Models\Supplier;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class PricingRulesTools
{
    /**
     * @var Generator
     */
    public Generator $faker;

    /**
     * @var Carbon
     */
    public Carbon $today;

    /**
     *
     */
    public function __construct()
    {
        $this->faker = Factory::create();
        $this->today = Carbon::now();
    }

    /**
     * @param array $query
     * @param int $channelId
     * @param int $supplierId
     * @return array
     */
    public function rules(array $query, int $channelId, int $supplierId): array
    {
        $generalTools = new GeneralTools();

        $today = now();

        $checkIn = $query['checkin'];

        $checkOut = $query['checkout'];

        $destination = $query['destination'] ?? null;

        $latitude = $query['latitude'] ?? null;

        $longitude = $query['longitude'] ?? null;

        $radius = $query['radius'] ?? null;

        if ($latitude && $longitude && $radius) {
            $geography = new Geography();

            $destination = $geography->findTheClosestCityInRadius($latitude, $longitude, $radius);
        }

        $carbonCheckIn = Carbon::parse($checkIn);

        $daysUntilDeparture = $today->diffInDays($carbonCheckIn);

        $nights = Carbon::parse($query['checkout'])->diffInDays($carbonCheckIn);

        $rating = (float)$query['rating'] ?? 4.0;

        $numberOfRooms = count($query['occupancy']);

        $totalGuests = $generalTools->calcTotalNumberOfGuestsInAllRooms($query['occupancy']);

        return PricingRule::with('conditions')
            ->whereHas('conditions', function (Builder $q) use (
                $supplierId,
                $channelId,
                $destination,
                $checkIn,
                $checkOut,
                $totalGuests,
                $daysUntilDeparture,
                $nights,
                $rating,
                $numberOfRooms
            ) {
                $q->where(function (Builder $q) use ($supplierId) {
                    $q->whereNot('field', 'supplier_id')
                        ->orWhere(function (Builder $q) use ($supplierId) {
                            $q->where('field', 'supplier_id')
                                ->where('compare', '=')
                                ->where('value_from', $supplierId);
                        });
                });

                $q->where(function (Builder $q) use ($channelId) {
                    $q->whereNot('field', 'channel_id')
                        ->orWhere(function (Builder $q) use ($channelId) {
                            $q->where('field', 'channel_id')
                                ->where('compare', '=')
                                ->where('value_from', $channelId);
                        });
                });

                $q->where(function (Builder $q) use ($destination) {
                    $q->whereNot('field', 'destination')
                        ->orWhere(function (Builder $q) use ($destination) {
                            $q->where('field', 'destination')
                                ->where('compare', '=')
                                ->where('value_from', $destination);
                        });
                });

                $q->where(function (Builder $q) use ($checkIn, $checkOut) {
                    $q->whereNot('field', 'travel_date')
                        ->orWhere(function (Builder $q) use ($checkIn) {
                            $q->where('field', 'travel_date')
                                ->where('compare', '=')
                                ->whereRaw('STR_TO_DATE(value_from, "%Y-%m-%d") = ?', [$checkIn]);
                        })
                        ->orWhere(function (Builder $q) use ($checkIn) {
                            $q->where('field', 'travel_date')
                                ->where('compare', '<')
                                ->whereRaw('STR_TO_DATE(value_from, "%Y-%m-%d") < ?', [$checkIn]);
                        })
                        ->orWhere(function (Builder $q) use ($checkIn) {
                            $q->where('field', 'travel_date')
                                ->where('compare', '>')
                                ->whereRaw('STR_TO_DATE(value_from, "%Y-%m-%d") > ?', [$checkIn]);
                        })
                        ->orWhere(function (Builder $q) use ($checkIn, $checkOut) {
                            $q->where('field', 'travel_date')
                                ->where('compare', 'between')
                                ->whereRaw('STR_TO_DATE(value_from, "%Y-%m-%d") <= ?', [$checkIn])
                                ->whereRaw('STR_TO_DATE(value_to, "%Y-%m-%d") >= ?', [$checkOut]);
                        });
                });

                $q->where(function (Builder $q) use ($checkIn, $checkOut) {
                    $q->whereNot('field', 'booking_date')
                        ->orWhere(function (Builder $q) use ($checkIn) {
                            $q->where('field', 'booking_date')
                                ->where('compare', '=')
                                ->whereRaw('STR_TO_DATE(value_from, "%Y-%m-%d") = ?', [$checkIn]);
                        })
                        ->orWhere(function (Builder $q) use ($checkIn) {
                            $q->where('field', 'booking_date')
                                ->where('compare', '<')
                                ->whereRaw('STR_TO_DATE(value_from, "%Y-%m-%d") < ?', [$checkIn]);
                        })
                        ->orWhere(function (Builder $q) use ($checkIn) {
                            $q->where('field', 'booking_date')
                                ->where('compare', '>')
                                ->whereRaw('STR_TO_DATE(value_from, "%Y-%m-%d") > ?', [$checkIn]);
                        })
                        ->orWhere(function (Builder $q) use ($checkIn, $checkOut) {
                            $q->where('field', 'booking_date')
                                ->where('compare', 'between')
                                ->whereRaw('STR_TO_DATE(value_from, "%Y-%m-%d") <= ?', [$checkIn])
                                ->whereRaw('STR_TO_DATE(value_to, "%Y-%m-%d") >= ?', [$checkOut]);
                        });
                });

                $q->where(function (Builder $q) use ($totalGuests) {
                    $q->whereNot('field', 'total_guests')
                        ->orWhere(function (Builder $q) use ($totalGuests) {
                            $q->where('field', 'total_guests')
                                ->where('compare', '=')
                                ->where('value_from', $totalGuests);
                        })
                        ->orWhere(function (Builder $q) use ($totalGuests) {
                            $q->where('field', 'total_guests')
                                ->where('compare', '<')
                                ->where('value_from', '<', $totalGuests);
                        })
                        ->orWhere(function (Builder $q) use ($totalGuests) {
                            $q->where('field', 'total_guests')
                                ->where('compare', '>')
                                ->where('value_from', '>', $totalGuests);
                        })
                        ->orWhere(function (Builder $q) use ($totalGuests) {
                            $q->where('field', 'total_guests')
                                ->where('compare', 'between')
                                ->where('value_from', '<=', $totalGuests);
                        });
                });

                $q->where(function (Builder $q) use ($daysUntilDeparture) {
                    $q->whereNot('field', 'days_until_departure')
                        ->orWhere(function (Builder $q) use ($daysUntilDeparture) {
                            $q->where('field', 'days_until_departure')
                                ->where('compare', '=')
                                ->where('value_from', $daysUntilDeparture);
                        })
                        ->orWhere(function (Builder $q) use ($daysUntilDeparture) {
                            $q->where('field', 'days_until_departure')
                                ->where('compare', '<')
                                ->where('value_from', '<', $daysUntilDeparture);
                        })
                        ->orWhere(function (Builder $q) use ($daysUntilDeparture) {
                            $q->where('field', 'days_until_departure')
                                ->where('compare', '>')
                                ->where('value_from', '>', $daysUntilDeparture);
                        })
                        ->orWhere(function (Builder $q) use ($daysUntilDeparture) {
                            $q->where('field', 'days_until_departure')
                                ->where('compare', 'between')
                                ->where('value_from', '<=', $daysUntilDeparture);
                        });
                });

                $q->where(function (Builder $q) use ($nights) {
                    $q->whereNot('field', 'nights')
                        ->orWhere(function (Builder $q) use ($nights) {
                            $q->where('field', 'nights')
                                ->where('compare', '=')
                                ->where('value_from', $nights);
                        })
                        ->orWhere(function (Builder $q) use ($nights) {
                            $q->where('field', 'nights')
                                ->where('compare', '<')
                                ->where('value_from', '<', $nights);
                        })
                        ->orWhere(function (Builder $q) use ($nights) {
                            $q->where('field', 'nights')
                                ->where('compare', '>')
                                ->where('value_from', '>', $nights);
                        })
                        ->orWhere(function (Builder $q) use ($nights) {
                            $q->where('field', 'nights')
                                ->where('compare', 'between')
                                ->where('value_from', '<=', $nights);
                        });
                });

                $q->where(function (Builder $q) use ($rating) {
                    $q->whereNot('field', 'rating')
                        ->orWhere(function (Builder $q) use ($rating) {
                            $q->where('field', 'rating')
                                ->where('compare', '=')
                                ->where('value_from', $rating);
                        })
                        ->orWhere(function (Builder $q) use ($rating) {
                            $q->where('field', 'rating')
                                ->where('compare', '<')
                                ->where('value_from', '<', $rating);
                        })
                        ->orWhere(function (Builder $q) use ($rating) {
                            $q->where('field', 'rating')
                                ->where('compare', '>')
                                ->where('value_from', '>', $rating);
                        })
                        ->orWhere(function (Builder $q) use ($rating) {
                            $q->where('field', 'rating')
                                ->where('compare', 'between')
                                ->where('value_from', '<=', $rating);
                        });
                });

                $q->where(function (Builder $q) use ($numberOfRooms) {
                    $q->whereNot('field', 'number_of_rooms')
                        ->orWhere(function (Builder $q) use ($numberOfRooms) {
                            $q->where('field', 'number_of_rooms')
                                ->where('compare', '=')
                                ->where('value_from', $numberOfRooms);
                        })
                        ->orWhere(function (Builder $q) use ($numberOfRooms) {
                            $q->where('field', 'number_of_rooms')
                                ->where('compare', '<')
                                ->where('value_from', '<', $numberOfRooms);
                        })
                        ->orWhere(function (Builder $q) use ($numberOfRooms) {
                            $q->where('field', 'number_of_rooms')
                                ->where('compare', '>')
                                ->where('value_from', '>', $numberOfRooms);
                        })
                        ->orWhere(function (Builder $q) use ($numberOfRooms) {
                            $q->where('field', 'number_of_rooms')
                                ->where('compare', 'between')
                                ->where('value_from', '<=', $numberOfRooms);
                        });
                });
            })->where(function (Builder $q) use ($query) {
                $q->where('rule_start_date', '<=', $query['checkin'])
                    ->where('rule_expiration_date', '>=', $query['checkout']);
            })->get()->toArray();
    }

    /**
     * @return string[]
     */
    public function getManipulablePriceTypeKeys(): array
    {
        return ['total_price', 'net_price', 'rate_price'];
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
        return ['per_guest', 'per_room', 'per_night'];
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
            'meal_plan'
        ];
    }

    /**
     * @param $name
     * @return array
     */
    public function generatePricingRuleData($name): array
    {
        return [
            'name' => "Pricing rule $name",
            'rule_start_date' => $this->today->copy()->toDateString(),
            'rule_expiration_date' => $this->today->copy()->addDays(rand(30, 60))->toDateString(),
            'manipulable_price_type' => $this->faker->randomElement($this->getManipulablePriceTypeKeys()),
            'price_value' => rand(1, 100),
            'price_value_type' => $this->faker->randomElement($this->getPriceValueTypeKeys()),
            'price_value_target' => $this->faker->randomElement($this->getPriceValueTargetKeys())
        ];
    }

    /**
     * @param int|null $giataId
     * @return array
     */
    public function generatePricingRuleConditionsData(int $giataId = null): array
    {
        $pricingRuleConditionsData = [];

        $randPricingRuleConditionFields = $this->faker->randomElements($this->getPricingRuleConditionFields(), rand(1, 14));

        foreach ($randPricingRuleConditionFields as $field) {
            $pricingRuleConditionsData[] = $this->pricingRuleConditionApplyLogic($field, $giataId);
        }

        return $pricingRuleConditionsData;
    }

    /**
     * @return array
     */
    public function generatePricingRuleConditionData(): array
    {
        $field = $this->faker->randomElement($this->getPricingRuleConditionFields());

        return $this->pricingRuleConditionApplyLogic($field);
    }

    /**
     * @param string $field
     * @param int|null $giataId
     * @return array
     */
    protected function pricingRuleConditionApplyLogic(string $field, int $giataId = null): array
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
        } else if (in_array($field, ['travel_date', 'booking_date'])) {
            $condition['value_from'] = $this->today->copy()->addDay()->toDateString();

            $condition['value_to'] = match ($compare) {
                '=', '<', '>' => null,
                'between' => $this->today->copy()->addDays(rand(2, 7))->toDateString()
            };
        } else if ($field === 'total_guests') {
            $condition['value_from'] = rand(3, 4);

            $condition['value_to'] = match ($compare) {
                '=', '<', '>' => null,
                'between' => rand(5, 8)
            };
        } else if ($field === 'days_until_departure') {
            $condition['value_from'] = rand(1, 8);

            $condition['value_to'] = match ($compare) {
                '=', '<', '>' => null,
                'between' => rand(9, 16)
            };
        } else if ($field === 'nights') {
            $condition['value_from'] = rand(1, 6);

            $condition['value_to'] = match ($compare) {
                '=', '<', '>' => null,
                'between' => rand(7, 14)
            };
        } else if ($field === 'rating') {
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
