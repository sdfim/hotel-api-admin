<?php

namespace Modules\API\PricingRules\Expedia;

use App\Models\PricingRules;

class ExpediaPricingRulesApplier
{

    public function test(): array
    {
        $giataId = 82024226;
        $channelId = 1;
        $requestObject = '{
            "checkin": "2023-11-10",
            "checkout": "2023-11-12",
            "destination": "New York",
            "rating": "4.5",
            "occupancy": [
                {
                    "adults": 4,
                    "children": 1
                },
                {
                    "adults": 2
                },
                {
                    "adults": 1
                }
            ]
        }';
        $pricingObject = '{
            "2": {
                "nightly": [
                    [
                        {
                            "type": "base_rate",
                            "value": "709.00",
                            "currency": "USD"
                        },
                        {
                            "type": "tax_and_service_fee",
                            "value": "108.07",
                            "currency": "USD"
                        }
                    ],
                    [
                        {
                            "type": "base_rate",
                            "value": "709.00",
                            "currency": "USD"
                        },
                        {
                            "type": "tax_and_service_fee",
                            "value": "108.07",
                            "currency": "USD"
                        }
                    ]
                ],
                "fees": {
                    "mandatory_fee": {
                        "billable_currency": {
                            "value": "70.00",
                            "currency": "USD"
                        },
                        "request_currency": {
                            "value": "70.00",
                            "currency": "USD"
                        }
                    }
                },
                "totals": {
                    "inclusive": {
                        "billable_currency": {
                            "value": "1634.14",
                            "currency": "USD"
                        },
                        "request_currency": {
                            "value": "1634.14",
                            "currency": "USD"
                        }
                    },
                    "exclusive": {
                        "billable_currency": {
                            "value": "1418.00",
                            "currency": "USD"
                        },
                        "request_currency": {
                            "value": "1418.00",
                            "currency": "USD"
                        }
                    },
                    "property_fees": {
                        "billable_currency": {
                            "value": "70.00",
                            "currency": "USD"
                        },
                        "request_currency": {
                            "value": "70.00",
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
     * @param string $pricingObject
     * @return array
     */
    public function apply(int $giataId, int $channelId, string $requestObject, string $pricingObject): array
    {
        $requestArray = json_decode($requestObject, true);
        $pricingArray = json_decode($pricingObject, true)['2'];

        $checkInDate = $requestArray['checkin'];
        $checkOutDate = $requestArray['checkout'];
        $rating = (float)$requestArray['rating'];
        $requiredRoomCount = count($requestArray['occupancy']);
        // total number of guests in all requested rooms
        $totalNumberOfGuests = self::countTotalNumberOfGuests($requestArray['occupancy']);
        $numberOfNights = count($pricingArray['nightly']);

        // supplier_id=1(is Expedia by default from seeder)
        $pricingRules = PricingRules::where('supplier_id', 1)
            ->where('property', $giataId)
            ->where('channel_id', $channelId)
            ->where('nights', '>=', $numberOfNights)
            ->where('total_guests', '>=', $totalNumberOfGuests)
            ->where('number_rooms', '>=', $requiredRoomCount)
            ->where('rating', '>=', $rating)
            ->whereDate('rule_start_date', '<=', $checkInDate)
            ->whereDate('rule_expiration_date', '>=', $checkOutDate)
            ->get();

        $inclusiveRoomTotal = (float)$pricingArray['totals']['inclusive']['billable_currency']['value'];
        $exclusiveRoomTotal = (float)$pricingArray['totals']['exclusive']['billable_currency']['value'];
        $totalRoomFees = (float)$pricingArray['totals']['property_fees']['billable_currency']['value'];
        $totalRoomTaxes = ($inclusiveRoomTotal - $exclusiveRoomTotal) - $totalRoomFees;

        $result = [
            'total_price' => 0,
            'total_tax' => 0,
            'total_fees' => 0,
            'total_net' => 0,
            'currency' => (string)$pricingArray['totals']['inclusive']['billable_currency']['currency']
        ];

        if ($pricingRules->count() === 0) {
            $result['total_price'] = round($inclusiveRoomTotal * $requiredRoomCount, 2);
            $result['total_tax'] = round($totalRoomTaxes * $requiredRoomCount, 2);
            $result['total_fees'] = round($totalRoomFees * $requiredRoomCount, 2);
            $result['total_net'] = round($exclusiveRoomTotal * $requiredRoomCount, 2);

            return $result;
        }

        // If $pricingRules->count > 0 then modifying $pricingArray
        foreach ($pricingRules as $pricingRule) {
            $priceValueTypeToApply = (string)$pricingRule['price_value_type_to_apply'];
            $priceValueToApply = (float)$pricingRule['price_value_to_apply'];
            $priceTypeToApply = (string)$pricingRule['price_type_to_apply'];

            // these values are calculated in the same way for all cases below, therefore they are moved to the top from each closure
            $result['total_tax'] += $totalRoomTaxes * $requiredRoomCount;
            $result['total_fees'] += $totalRoomFees * $requiredRoomCount;

            if ($priceValueTypeToApply === 'percentage') {
                // in case when supplier is Expedia total_price and rate_price should be calculated the same way
                if ($priceTypeToApply === 'total_price' || $priceTypeToApply === 'rate_price') {
                    /*if we increase the total_price by a certain percentage, then each of the indicators that affect
                    the total_price must also be increased by a given percentage, except for total_fees(it's a fixed value)*/
                    $result['total_price'] += ($inclusiveRoomTotal + (($inclusiveRoomTotal * $priceValueToApply) / 100)) * $requiredRoomCount;
                    $result['total_net'] += $exclusiveRoomTotal * $requiredRoomCount;
                }
                if ($priceTypeToApply === 'net_price') {
                    /*if we increase the net_price by a certain percentage, then the rest of indicators should
                    remain unchanged*/
                    $result['total_net'] += ($exclusiveRoomTotal + (($exclusiveRoomTotal * $priceValueToApply) / 100)) * $requiredRoomCount;
                    $result['total_price'] += $result['total_net'] + $result['total_tax'] + $result['total_fees'];
                }
            } else {
                // this value only available when $priceValueTypeToApply === 'fixed_value'
                $priceValueFixedTypeToApply = (string)$pricingRule['price_value_fixed_type_to_apply'];

                if ($priceTypeToApply === 'total_price' || $priceTypeToApply === 'rate_price') {
                    $inclusiveRoomsTotal = $inclusiveRoomTotal * $requiredRoomCount;
                    if ($priceValueFixedTypeToApply === 'per_guest') {
                        $result['total_price'] += $inclusiveRoomsTotal + ($totalNumberOfGuests * $priceValueToApply);
                    }
                    if ($priceValueFixedTypeToApply === 'per_room') {
                        $result['total_price'] += $inclusiveRoomsTotal + ($requiredRoomCount * $priceValueToApply);
                    }
                    if ($priceValueFixedTypeToApply === 'per_night') {
                        $result['total_price'] += $inclusiveRoomsTotal + ($numberOfNights * $priceValueToApply);
                    }

                    // the same calculation for all cases above
                    $result['total_net'] += $exclusiveRoomTotal * $requiredRoomCount;
                }
                if ($priceTypeToApply === 'net_price') {
                    $exclusiveRoomsTotal = $exclusiveRoomTotal * $requiredRoomCount;
                    if ($priceValueFixedTypeToApply === 'per_guest') {
                        $result['total_net'] += $exclusiveRoomsTotal + ($totalNumberOfGuests * $priceValueToApply);
                    }
                    if ($priceValueFixedTypeToApply === 'per_room') {
                        $result['total_net'] += $exclusiveRoomsTotal + ($requiredRoomCount * $priceValueToApply);
                    }
                    if ($priceValueFixedTypeToApply === 'per_night') {
                        $result['total_net'] += $exclusiveRoomsTotal + ($numberOfNights * $priceValueToApply);
                    }

                    // the same calculation for all cases above
                    $result['total_price'] += $result['total_net'] + $result['total_tax'] + $result['total_fees'];
                }
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
    public static function countTotalNumberOfGuests(array $rooms): int
    {
        $totalNumberOfGuests = 0;

        foreach ($rooms as $room) {
            foreach ($room as $roomGuestsNumber) {
                $totalNumberOfGuests += (int)$roomGuestsNumber;
            }
        }

        return $totalNumberOfGuests;
    }
}
