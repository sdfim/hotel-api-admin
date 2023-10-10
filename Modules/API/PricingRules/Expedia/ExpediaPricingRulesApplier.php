<?php

namespace Modules\API\PricingRules\Expedia;

use App\Models\PricingRules;

class ExpediaPricingRulesApplier
{
    /**
     * @param int $giataId
     * @param int $channelId
     * @param object $requestObject
     * @param object $pricingObject
     * @return array
     */
    public function apply(int $giataId, int $channelId, object $requestObject, object $pricingObject): array
    {
        $pricingArray = json_decode($pricingObject, true)['2'];
        $requestArray = json_decode($requestObject, true);

        $checkInDate = $requestArray['checkin'];
        $checkOutDate = $requestArray['checkout'];
        $rating = (int)$requestArray['rating'];
        $requiredRoomCount = count($requestArray['occupancy']);
        // total number of guests in all requested rooms
        $totalNumberOfGuests = self::countTotalNumberOfGuests($requestArray['occupancy']);
        $numberOfNights = count($pricingArray['nightly']);

        // TODO: add more detailed requirements to find the proper rule
        // supplier_id=1(is Expedia by default from seeder)
        $pricingRules = PricingRules::where('supplier_id', 1)
            ->where('property', $giataId)
            ->where('channel_id', $channelId)
            ->where('rating', '>=', $rating)
            ->whereDate('rule_start_date', '>=', $checkInDate)
            ->whereDate('rule_expiration_date', '<=', $checkOutDate)
            ->get();

        $inclusiveRoomTotal = (float)$pricingArray['totals']['inclusive']['billable_currency']['value'];
        $exclusiveRoomTotal = (float)$pricingArray['totals']['exclusive']['billable_currency']['value'];
        $totalRoomFees = (float)$pricingArray['totals']['property_fees']['billable_currency']['value'];
        $totalRoomTax = ($inclusiveRoomTotal - $exclusiveRoomTotal) - $totalRoomFees;

        $result = [
            'total_price' => 0,
            'total_tax' => 0,
            'total_fees' => 0,
            'total_net' => 0,
            'currency' => (string)$pricingArray['totals']['inclusive']['billable_currency']['currency']
        ];

        // If $pricingRules->count > 0 then modifying $pricingArray
        if ($pricingRules->count() > 0) {
            foreach ($pricingRules as $pricingRule) {
                $priceValueTypeToApply = (string)$pricingRule['price_value_type_to_apply'];
                $priceValueToApply = (float)$pricingRule['price_value_to_apply'];
                $priceTypeToApply = (string)$pricingRule['price_type_to_apply'];

                // these values are calculated in the same way for all cases, therefore they are moved to the top from each closure
                $result['total_tax'] += $totalRoomTax * $requiredRoomCount;
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
                        if ($priceValueFixedTypeToApply === 'per_guest') {
                            $result['total_price'] += ($inclusiveRoomTotal + ($totalNumberOfGuests * $priceValueToApply)) * $requiredRoomCount;
                        }
                        if ($priceValueFixedTypeToApply === 'per_room') {
                            $result['total_price'] += ($inclusiveRoomTotal + ($requiredRoomCount * $priceValueToApply)) * $requiredRoomCount;
                        }
                        if ($priceValueFixedTypeToApply === 'per_night') {
                            $result['total_price'] += ($inclusiveRoomTotal + ($numberOfNights * $priceValueToApply)) * $requiredRoomCount;
                        }

                        // the same calculation for all cases
                        $result['total_net'] += $exclusiveRoomTotal * $requiredRoomCount;
                    }
                    if ($priceTypeToApply === 'net_price') {
                        if ($priceValueFixedTypeToApply === 'per_guest') {
                            $result['total_net'] += ($exclusiveRoomTotal + ($totalNumberOfGuests * $priceValueToApply)) * $requiredRoomCount;
                        }
                        if ($priceValueFixedTypeToApply === 'per_room') {
                            $result['total_net'] += ($exclusiveRoomTotal + ($requiredRoomCount * $priceValueToApply)) * $requiredRoomCount;
                        }
                        if ($priceValueFixedTypeToApply === 'per_night') {
                            $result['total_net'] += ($exclusiveRoomTotal + ($numberOfNights * $priceValueToApply)) * $requiredRoomCount;
                        }

                        // the same calculation for all cases
                        $result['total_price'] += $result['total_net'] + $result['total_tax'] + $result['total_fees'];
                    }
                }
            }
        } else {
            $result = [
                'total_price' => $inclusiveRoomTotal * $requiredRoomCount,
                'total_tax' => $totalRoomTax * $requiredRoomCount,
                'total_fees' => $totalRoomFees * $requiredRoomCount,
                'total_net' => $exclusiveRoomTotal * $requiredRoomCount,
                'currency' => (string)$pricingArray['totals']['inclusive']['billable_currency']['currency']
            ];
        }

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

    /**
     * @param int|float $number
     * @param int $percentage
     * @return int|float
     */
    public static function getPercentFromNumber(int|float $number, int $percentage): int|float
    {
        return ($number * $percentage) / 100;
    }
}
