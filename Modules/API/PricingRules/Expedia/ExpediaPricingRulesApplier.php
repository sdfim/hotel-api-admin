<?php

namespace Modules\API\PricingRules\Expedia;

use App\Models\PricingRules;
use Modules\API\PricingRules\PricingRulesApplierInterface;

class ExpediaPricingRulesApplier implements PricingRulesApplierInterface
{

    public function test(): array
    {
        $giataId = 78742866;
        $channelId = 1;
        $requestObject = '{
            "checkin": "2023-11-11",
            "checkout": "2023-11-12",
            "destination": "New York",
            "rating": "4.5",
            "occupancy": [
                {
                    "adults": 2,
                    "children": 1
                },
                {
                    "adults": 2
                },
                {
                    "adults": 2
                },
                {
                    "adults": 4
                }
            ]
        }';
        $pricingObject = '{
            "4": {
                "nightly": [
                    [
                        {
                            "type": "extra_person_fee",
                            "value": "60.00",
                            "currency": "USD"
                        },
                        {
                            "type": "base_rate",
                            "value": "471.20",
                            "currency": "USD"
                        },
                        {
                            "type": "tax_and_service_fee",
                            "value": "85.85",
                            "currency": "USD"
                        }
                    ]
                ],
                "fees": {
                    "resort_fee": {
                        "request_currency": {
                            "value": "34.42",
                            "currency": "USD"
                        },
                        "billable_currency": {
                            "value": "34.42",
                            "currency": "USD"
                        }
                    }
                },
                "totals": {
                    "property_fees": {
                        "request_currency": {
                            "value": "34.42",
                            "currency": "USD"
                        },
                        "billable_currency": {
                            "value": "34.42",
                            "currency": "USD"
                        }
                    },
                    "exclusive": {
                        "request_currency": {
                            "value": "531.20",
                            "currency": "USD"
                        },
                        "billable_currency": {
                            "value": "531.20",
                            "currency": "USD"
                        }
                    },
                    "inclusive": {
                        "request_currency": {
                            "value": "617.05",
                            "currency": "USD"
                        },
                        "billable_currency": {
                            "value": "617.05",
                            "currency": "USD"
                        }
                    }
                }
            },
            "2": {
                "nightly": [
                    [
                        {
                            "type": "base_rate",
                            "value": "471.20",
                            "currency": "USD"
                        },
                        {
                            "type": "tax_and_service_fee",
                            "value": "77.00",
                            "currency": "USD"
                        }
                    ]
                ],
                "fees": {
                    "resort_fee": {
                        "request_currency": {
                            "value": "34.42",
                            "currency": "USD"
                        },
                        "billable_currency": {
                            "value": "34.42",
                            "currency": "USD"
                        }
                    }
                },
                "totals": {
                    "property_fees": {
                        "request_currency": {
                            "value": "34.42",
                            "currency": "USD"
                        },
                        "billable_currency": {
                            "value": "34.42",
                            "currency": "USD"
                        }
                    },
                    "exclusive": {
                        "request_currency": {
                            "value": "471.20",
                            "currency": "USD"
                        },
                        "billable_currency": {
                            "value": "471.20",
                            "currency": "USD"
                        }
                    },
                    "inclusive": {
                        "request_currency": {
                            "value": "548.20",
                            "currency": "USD"
                        },
                        "billable_currency": {
                            "value": "548.20",
                            "currency": "USD"
                        }
                    }
                }
            },
            "3": {
                "nightly": [
                    [
                        {
                            "type": "extra_person_fee",
                            "value": "30.00",
                            "currency": "USD"
                        },
                        {
                            "type": "base_rate",
                            "value": "471.20",
                            "currency": "USD"
                        },
                        {
                            "type": "tax_and_service_fee",
                            "value": "81.43",
                            "currency": "USD"
                        }
                    ]
                ],
                "fees": {
                    "resort_fee": {
                        "request_currency": {
                            "value": "34.42",
                            "currency": "USD"
                        },
                        "billable_currency": {
                            "value": "34.42",
                            "currency": "USD"
                        }
                    }
                },
                "totals": {
                    "property_fees": {
                        "request_currency": {
                            "value": "34.42",
                            "currency": "USD"
                        },
                        "billable_currency": {
                            "value": "34.42",
                            "currency": "USD"
                        }
                    },
                    "exclusive": {
                        "request_currency": {
                            "value": "501.20",
                            "currency": "USD"
                        },
                        "billable_currency": {
                            "value": "501.20",
                            "currency": "USD"
                        }
                    },
                    "inclusive": {
                        "request_currency": {
                            "value": "582.63",
                            "currency": "USD"
                        },
                        "billable_currency": {
                            "value": "582.63",
                            "currency": "USD"
                        }
                    }
                }
            }
        }';

        return $this->apply($giataId, $channelId, $requestObject, $pricingObject);
    }

    /**
     * @param int $giataId
     * @param int $channelId
     * @param string $requestObject
     * @param string $roomsPricingObject
     * @return array{
     *      total_price: float|int,
     *      total_tax: float|int,
     *      total_fees: float|int,
     *      total_net: float|int,
     *      currency: string
     *  }
     */
    public function apply(int $giataId, int $channelId, string $requestObject, string $roomsPricingObject): array
    {
        $requestArray = json_decode($requestObject, true);
        $roomsPricingArray = json_decode($roomsPricingObject, true);
        $firstRoomCapacityKey = array_key_first($roomsPricingArray);

        /**
         * @var array{total_price: float|int,total_tax: float|int,total_fees: float|int,total_net: float|int,currency: string} $result
         */
        $result = [
            'total_price' => 0,
            'total_tax' => 0,
            'total_fees' => 0,
            'total_net' => 0,
            'currency' => (string)($roomsPricingArray[$firstRoomCapacityKey]['totals']['inclusive']['billable_currency']['currency'] ?? 'USD')
        ];

        $numberOfNights = count($roomsPricingArray[$firstRoomCapacityKey]['nightly']);
        $totalNumberOfGuestsInAllRooms = self::countTotalNumberOfGuestsInAllRooms($requestArray['occupancy']);
        $requiredRoomCount = count($requestArray['occupancy']);

        // supplier_id=1(is Expedia by default from seeder)
        $pricingRule = PricingRules::where('supplier_id', 1)
            ->where('property', $giataId)
            ->where('channel_id', $channelId)
            ->where('nights', '>=', $numberOfNights)
            ->where('total_guests', '>=', $totalNumberOfGuestsInAllRooms)
            ->where('number_rooms', '>=', $requiredRoomCount)
            ->where('rating', '>=', (float)$requestArray['rating'])
            ->whereDate('rule_start_date', '<=', $requestArray['checkin'])
            ->whereDate('rule_expiration_date', '>=', $requestArray['checkout'])
            ->first();

        $priceValueTypeToApply = (string)($pricingRule['price_value_type_to_apply'] ?? '');
        $priceValueToApply = (float)($pricingRule['price_value_to_apply'] ?? 0);
        $priceTypeToApply = (string)($pricingRule['price_type_to_apply'] ?? '');
        // this value only available when $priceValueTypeToApply === 'fixed_value'
        $priceValueFixedTypeToApply = (string)($pricingRule['price_value_fixed_type_to_apply'] ?? '');

        foreach ($requestArray['occupancy'] as $room) {
            $totalNumberOfGuestsInRoom = (int)array_sum($room);
            $roomTotals = self::calculateRoomTotals($roomsPricingArray[$totalNumberOfGuestsInRoom]);
            // these values are calculated in the same way for all cases below, therefore they are moved to the top from each closure
            $result['total_tax'] += $roomTotals['total_tax'];
            $result['total_fees'] += $roomTotals['total_fees'];

            if ($pricingRule) {
                if ($priceValueTypeToApply === 'percentage') {
                    if ($priceTypeToApply === 'total_price') {
                        $result['total_price'] += $roomTotals['total_price'] + (($roomTotals['total_price'] * $priceValueToApply) / 100);
                        $result['total_net'] += $roomTotals['total_net'];
                    }
                    // in case when supplier is Expedia total_price and rate_price should be calculated the same way
                    if ($priceTypeToApply === 'net_price' || $priceTypeToApply === 'rate_price') {
                        $totalNet = $roomTotals['total_net'] + (($roomTotals['total_net'] * $priceValueToApply) / 100);
                        $result['total_net'] += $totalNet;
                        $result['total_price'] += $totalNet + $roomTotals['total_tax'] + $roomTotals['total_fees'];
                    }
                } else {
                    if ($priceTypeToApply === 'total_price') {
                        if ($priceValueFixedTypeToApply === 'per_guest') {
                            $result['total_price'] += $roomTotals['total_price'] + ($totalNumberOfGuestsInRoom * $priceValueToApply);
                        }
                        if ($priceValueFixedTypeToApply === 'per_room') {
                            $result['total_price'] += $roomTotals['total_price'] + $priceValueToApply;
                        }
                        if ($priceValueFixedTypeToApply === 'per_night') {
                            $result['total_price'] += $roomTotals['total_price'] + ($numberOfNights * $priceValueToApply);
                        }

                        // the same calculation for all cases above
                        $result['total_net'] += $roomTotals['total_net'];
                    }
                    if ($priceTypeToApply === 'net_price' || $priceTypeToApply === 'rate_price') {
                        $totalNet = 0;
                        if ($priceValueFixedTypeToApply === 'per_guest') {
                            $totalNet = $roomTotals['total_net'] + ($totalNumberOfGuestsInRoom * $priceValueToApply);
                        }
                        if ($priceValueFixedTypeToApply === 'per_room') {
                            $totalNet = $roomTotals['total_net'] + $priceValueToApply;
                        }
                        if ($priceValueFixedTypeToApply === 'per_night') {
                            $totalNet = $roomTotals['total_net'] + ($numberOfNights * $priceValueToApply);
                        }

                        // the same calculation for all cases above
                        $result['total_net'] += $totalNet;
                        $result['total_price'] += $totalNet + $roomTotals['total_tax'] + $roomTotals['total_fees'];
                    }
                }
            } else {
                $result['total_price'] += $roomTotals['total_price'];
                $result['total_net'] += $roomTotals['total_net'];
            }
        }

        $result['total_price'] = round($result['total_price'], 2);
        $result['total_tax'] = round($result['total_tax'], 2);
        $result['total_fees'] = round($result['total_fees'], 2);
        $result['total_net'] = round($result['total_net'], 2);

        return $result;
    }


    /**
     * @param array $rooms
     * @return int
     */
    public static function countTotalNumberOfGuestsInAllRooms(array $rooms): int
    {
        $totalNumberOfGuests = 0;

        foreach ($rooms as $room) {
            foreach ($room as $roomGuestsNumber) {
                $totalNumberOfGuests += (int)$roomGuestsNumber;
            }
        }

        return $totalNumberOfGuests;
    }

    /**
     * @param array $roomPricing
     * @return array{total_price: float|int,total_tax: float|int,total_fees: float|int,total_net: float|int,currency: string}
     */
    public static function calculateRoomTotals(array $roomPricing): array
    {
        // in case when there is no any discount total_net = rate_price(amount of rates each night)
        /**
         * @var array{total_price: float|int,total_tax: float|int,total_fees: float|int,total_net: float|int,currency: string} $totals
         */
        $totals = [
            'total_price' => 0,
            'total_tax' => 0,
            'total_fees' => 0,
            'total_net' => 0,
            'currency' => (string)($roomPricing['nightly'][0][0]['currency'] ?? 'USD'),
        ];

        foreach ($roomPricing['nightly'] as $night) {
            foreach ($night as $expenseItem) {
                $totals['total_price'] += $expenseItem['value'];
                if ($expenseItem['type'] === 'base_rate') {
                    $totals['total_net'] += $expenseItem['value'];
                }
                if ($expenseItem['type'] === 'tax_and_service_fee') {
                    $totals['total_tax'] += $expenseItem['value'];
                }
                if (!in_array($expenseItem['type'], ['base_rate', 'tax_and_service_fee'])) {
                    $totals['total_fees'] += $expenseItem['value'];
                }
            }
        }

        $totals['total_fees'] += (float)($roomPricing['totals']['property_fees']['billable_currency']['value'] ?? 0);

        return $totals;
    }
}
