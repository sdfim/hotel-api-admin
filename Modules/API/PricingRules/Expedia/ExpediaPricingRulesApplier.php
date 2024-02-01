<?php

namespace Modules\API\PricingRules\Expedia;

use Modules\API\PricingRules\PricingRulesApplierInterface;

class ExpediaPricingRulesApplier implements PricingRulesApplierInterface
{
    /**
     * @var array
     */
    private array $requestArray;
    /**
     * @var array
     */
    private array $pricingRules;

    public function __construct(array $requestArray, array $pricingRules)
    {
        $this->requestArray = $requestArray;
        $this->pricingRules = $pricingRules;
    }

    /**
     * @param int $giataId
     * @param array $roomsPricingArray
     * @param bool $b2b
     * @return array{
     *      total_price: float|int,
     *      total_tax: float|int,
     *      total_fees: float|int,
     *      total_net: float|int,
     *      affiliate_service_charge: float|int,
     *  }
     */
    public function apply(int $giataId, array $roomsPricingArray, bool $b2b = true): array
    {
        /**
         * @var array{total_price: float|int,total_tax: float|int,total_fees: float|int,total_net: float|int,affiliate_service_charge: float|int} $result
         */
        $result = [
            'total_price' => 0,
            'total_tax' => 0,
            'total_fees' => 0,
            'total_net' => 0,
            'affiliate_service_charge' => 0,
        ];

        foreach ($this->pricingRules as $pricingRule) {
            $firstRoomCapacityKey = array_key_first($roomsPricingArray);

            $numberOfNights = count($roomsPricingArray[$firstRoomCapacityKey]['nightly']);

            // TODO: find out if we can find it in Expedia response and compare with rule details
            $hotelName = 'Test Hotel';

            $rateCode = 'Test rate code';

            $roomType = 'Test room type';

            $mealPlan = 'Test meal plan';

            $validPricingRule = true;

            foreach ($pricingRule['conditions'] as $condition) {
                $validPricingRule = match ($condition['field']) {
                    'property' => $validPricingRule && $condition['value_from'] === $hotelName,
                    'rate_code' => $validPricingRule && $condition['value_from'] === $rateCode,
                    'room_type' => $validPricingRule && $condition['value_from'] === $roomType,
                    'meal_plan' => $validPricingRule && $condition['value_from'] === $mealPlan
                };
            }

            $priceValueType = (string)($pricingRule['price_value_type'] ?? '');

            $priceValue = (float)($pricingRule['price_value'] ?? 0);

            $manipulablePriceType = (string)($pricingRule['manipulable_price_type'] ?? '');

            // this value only available when $priceValueTypeToApply === 'fixed_value'
            $priceValueTarget = (string)($pricingRule['price_value_target'] ?? '');

            foreach ($this->requestArray['occupancy'] as $room) {
                $totalNumberOfGuestsInRoom = (int)array_sum($room);

                // TODO: why is the key adults_number-children_age_1,children_age_2 ... children_age_n
                // TODO: is $totalNumberOfGuestsInRoom is correct key for a $roomsPricingArray or should it be the same as in b2b case

                $roomsPricingKey = isset($room['children_ages']) ? $room['adults'] . '-' . implode(',', $room['children_ages']) : $room['adults'];

                $roomTotals = $this->calculateRoomTotals($roomsPricingArray[$roomsPricingKey]);

                // these values are calculated in the same way for all cases below, therefore they are moved to the top from each closure
                $result['total_tax'] += $roomTotals['total_tax'];

                $result['total_fees'] += $roomTotals['total_fees'];

                if ($validPricingRule) {
                    // calculate pricing for each room from request
                    if ($b2b) {
                        // TODO: find out  correct logic for b2b, if we need to apply pricing rules, then calculations should be the same as for the not b2b
                        if ($priceValueType === 'percentage') {
                            $priceValueFromTotal = ($roomTotals['total_price'] * $priceValue) / 100;

                            $result['total_price'] += match ($priceValueTarget) {
                                'per_guest' => $roomTotals['total_price'] + ($totalNumberOfGuestsInRoom * $priceValueFromTotal),
                                'per_room' => $roomTotals['total_price'] + $priceValueFromTotal,
                                'per_night' => $roomTotals['total_price'] + ($numberOfNights * $priceValueFromTotal)
                            };

                            $result['total_net'] += $roomTotals['total_net'];

                            if ($manipulablePriceType === 'total_price') {
                                $result['affiliate_service_charge'] += ($roomTotals['total_price'] * $priceValue) / 100;
                            }

                            // in case when supplier is Expedia total_price and rate_price should be calculated the same way
                            if ($manipulablePriceType === 'net_price' || $manipulablePriceType === 'rate_price') {
                                $result['affiliate_service_charge'] += ($roomTotals['total_net'] * $priceValue) / 100;
                            }
                        } else {
                            $result['affiliate_service_charge'] += match ($priceValueTarget) {
                                'per_guest' => $totalNumberOfGuestsInRoom * $priceValue,
                                'per_room' => $priceValue,
                                'per_night' => $numberOfNights * $priceValue
                            };
                        }
                    } else {
                        if ($manipulablePriceType === 'total_price') {
                            $priceValueFromTotal = ($roomTotals['total_price'] * $priceValue) / 100;

                            $result['total_price'] += match ($priceValueTarget) {
                                'per_guest' => match ($priceValueType) {
                                    'percentage' => $roomTotals['total_price'] + ($totalNumberOfGuestsInRoom * $priceValueFromTotal),
                                    'fixed_value' => $roomTotals['total_price'] + ($totalNumberOfGuestsInRoom * $priceValue)
                                },
                                'per_room' => match ($priceValueType) {
                                    'percentage' => $roomTotals['total_price'] + $priceValueFromTotal,
                                    'fixed_value' => $roomTotals['total_price'] + $priceValue
                                },
                                'per_night' => match ($priceValueType) {
                                    'percentage' => $roomTotals['total_price'] + ($numberOfNights * $priceValueFromTotal),
                                    'fixed_value' => $roomTotals['total_price'] + ($numberOfNights * $priceValue)
                                }
                            };

                            // the same calculation for all cases above
                            $result['total_net'] += $roomTotals['total_net'];
                        }

                        // in case when supplier is Expedia total_price and rate_price should be calculated the same way
                        if ($manipulablePriceType === 'net_price' || $manipulablePriceType === 'rate_price') {
                            $priceValueFromTotalNet = ($roomTotals['total_net'] * $priceValue) / 100;

                            $totalNet = match ($priceValueTarget) {
                                'per_guest' => match ($priceValueType) {
                                    'percentage' => $roomTotals['total_net'] + ($totalNumberOfGuestsInRoom * $priceValueFromTotalNet),
                                    'fixed_value' => $roomTotals['total_net'] + ($totalNumberOfGuestsInRoom * $priceValue)
                                },
                                'per_room' => match ($priceValueType) {
                                    'percentage' => $roomTotals['total_net'] + $priceValueFromTotalNet,
                                    'fixed_value' => $roomTotals['total_net'] + $priceValue
                                },
                                'per_night' => match ($priceValueType) {
                                    'percentage' => $roomTotals['total_net'] + ($numberOfNights * $priceValueFromTotalNet),
                                    'fixed_value' => $roomTotals['total_net'] + ($numberOfNights * $priceValue),
                                },
                                'default' => 0
                            };

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
        }

        $result['total_price'] = round($result['total_price'], 2);

        $result['total_tax'] = round($result['total_tax'], 2);

        $result['total_fees'] = round($result['total_fees'], 2);

        $result['total_net'] = round($result['total_net'], 2);

        $result['affiliate_service_charge'] = round($result['affiliate_service_charge'], 2);

        return $result;
    }

    /**
     * @param array $roomPricing
     * @return array{total_price: float|int,total_tax: float|int,total_fees: float|int,total_net: float|int}
     */
    private function calculateRoomTotals(array $roomPricing): array
    {
        // in case when there is no any discount total_net = rate_price(amount of rates each night)
        /**
         * @var array{total_price: float|int,total_tax: float|int,total_fees: float|int,total_net: float|int} $totals
         */
        $totals = [
            'total_price' => 0,
            'total_tax' => 0,
            'total_fees' => 0,
            'total_net' => 0,
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
