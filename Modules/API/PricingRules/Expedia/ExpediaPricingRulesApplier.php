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
    private array $pricingRule;

    public function __construct(array $requestArray, array $pricingRule)
    {
        $this->requestArray = $requestArray;
        $this->pricingRule = $pricingRule;
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
        $pricingRule = $this->pricingRule[$giataId] ?? [];

        $firstRoomCapacityKey = array_key_first($roomsPricingArray);

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

        $numberOfNights = count($roomsPricingArray[$firstRoomCapacityKey]['nightly']);
        $totalNumberOfGuestsInAllRooms = self::countTotalNumberOfGuestsInAllRooms($this->requestArray['occupancy']);
        $requiredRoomCount = count($this->requestArray['occupancy']);

        // Previously, we attempted to locate and pass a pricing rule based on factors such as supplier, channel, property,
        // rule_start_date and rule_expiration date. Now, we are also evaluating whether the rule aligns with our requirements
        // based on criteria that we can calculate or derive from property data.
        $isValidPricingRule = $pricingRule && $pricingRule['nights'] >= $numberOfNights &&
            $pricingRule['total_guests'] >= $totalNumberOfGuestsInAllRooms &&
            $pricingRule['number_rooms'] >= $requiredRoomCount;

        $priceValueTypeToApply = (string)($pricingRule['price_value_type_to_apply'] ?? '');
        $priceValueToApply = (float)($pricingRule['price_value_to_apply'] ?? 0);
        $priceTypeToApply = (string)($pricingRule['price_type_to_apply'] ?? '');
        // this value only available when $priceValueTypeToApply === 'fixed_value'
        $priceValueFixedTypeToApply = (string)($pricingRule['price_value_fixed_type_to_apply'] ?? '');

        // calculate pricing for each room from request
        if ($b2b) {
            foreach ($this->requestArray['occupancy'] as $room) {
                $totalNumberOfGuestsInRoom = (int)array_sum($room);
                $roomTotals = self::calculateRoomTotals($roomsPricingArray[$totalNumberOfGuestsInRoom]);
                $result['total_price'] += $roomTotals['total_price'];
                $result['total_tax'] += $roomTotals['total_tax'];
                $result['total_fees'] += $roomTotals['total_fees'];
                $result['total_net'] += $roomTotals['total_net'];

                if ($isValidPricingRule) {
                    if ($priceValueTypeToApply === 'percentage') {
                        if ($priceTypeToApply === 'total_price') {
                            $result['affiliate_service_charge'] += ($roomTotals['total_price'] * $priceValueToApply) / 100;
                        }
                        // in case when supplier is Expedia total_price and rate_price should be calculated the same way
                        if ($priceTypeToApply === 'net_price' || $priceTypeToApply === 'rate_price') {
                            $result['affiliate_service_charge'] += ($roomTotals['total_net'] * $priceValueToApply) / 100;
                        }
                    } else {
                        if ($priceValueFixedTypeToApply === 'per_guest') {
                            $result['affiliate_service_charge'] += $totalNumberOfGuestsInRoom * $priceValueToApply;
                        }
                        if ($priceValueFixedTypeToApply === 'per_room') {
                            $result['affiliate_service_charge'] += $priceValueToApply;
                        }
                        if ($priceValueFixedTypeToApply === 'per_night') {
                            $result['affiliate_service_charge'] += $numberOfNights * $priceValueToApply;
                        }
                    }
                }
            }
        } else {
            foreach ($this->requestArray['occupancy'] as $room) {
                $totalNumberOfGuestsInRoom = (int)array_sum($room);
                $roomTotals = self::calculateRoomTotals($roomsPricingArray[$totalNumberOfGuestsInRoom]);
                // these values are calculated in the same way for all cases below, therefore they are moved to the top from each closure
                $result['total_tax'] += $roomTotals['total_tax'];
                $result['total_fees'] += $roomTotals['total_fees'];

                if ($isValidPricingRule) {
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
        }

        $result['total_price'] = round($result['total_price'], 2);
        $result['total_tax'] = round($result['total_tax'], 2);
        $result['total_fees'] = round($result['total_fees'], 2);
        $result['total_net'] = round($result['total_net'], 2);
        $result['affiliate_service_charge'] = round($result['affiliate_service_charge'], 2);

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
     * @return array{total_price: float|int,total_tax: float|int,total_fees: float|int,total_net: float|int}
     */
    public static function calculateRoomTotals(array $roomPricing): array
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
