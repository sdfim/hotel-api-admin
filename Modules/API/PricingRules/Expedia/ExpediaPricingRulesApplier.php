<?php

namespace Modules\API\PricingRules\Expedia;

use Modules\API\PricingRules\BasePricingRulesApplier;
use Modules\API\PricingRules\PricingRulesApplierInterface;

/**
 *
 */
class ExpediaPricingRulesApplier extends BasePricingRulesApplier implements PricingRulesApplierInterface
{
    /**
     * @param int $giataId
     * @param array $roomsPricingArray
     * @param bool $b2b
     * @return array{
     *      total_price: float|int,
     *      total_tax: float|int,
     *      total_fees: float|int,
     *      total_net: float|int,
     *      affiliate_service_charge: float|int
     *  }
     */
    public function apply(int $giataId, array $roomsPricingArray, bool $b2b = true): array
    {
        foreach ($this->pricingRules as $pricingRule) {
            $this->validPricingRule = $this->validPricingRule($pricingRule['conditions'], $giataId);

            $this->setPricingRuleValues($pricingRule);

            foreach ($this->requestArray['occupancy'] as $room) {
                $this->totalNumberOfGuestsInRoom = $this->totalNumberOfGuestsInRoom($room);

                $roomsPricingKey = isset($room['children_ages']) ? $room['adults'] . '-' . implode(',', $room['children_ages']) : $room['adults'];

                $this->roomTotals = $this->calculateRoomTotals($roomsPricingArray[$roomsPricingKey]);

                $this->applyPricingRulesLogic();
            }
        }

        return $this->totals($b2b);
    }

    /**
     * @param array $conditions
     * @param int $giataId
     * @return bool
     */
    private function validPricingRule(array $conditions, int $giataId): bool
    {
        $validPricingRule = true;

        foreach ($conditions as $condition) {
            if ($condition['field'] === 'property') {
                if ((int)$condition['value_from'] === $giataId) {
                    $validPricingRule = true;
                    break;
                } else {
                    $validPricingRule = false;
                }
            }
        }

        return $validPricingRule;
    }

    /**
     * Calculates total_price(net_price, fees, taxes)
     *
     * @param array $roomPricing
     * @return array{total_price: float|int,total_tax: float|int,total_fees: float|int,total_net: float|int}
     */
    private function calculateRoomTotals(array $roomPricing): array
    {
        /**
         * @var array{total_price: float|int,total_tax: float|int,total_fees: float|int,total_net: float|int} $totals
         */
        $totals = [
            'total_price' => 0,
            'total_tax' => 0,
            'total_fees' => 0,
            'total_net' => 0
        ];

        foreach ($roomPricing['nightly'] as $night) {
            foreach ($night as $expenseItem) {
                $totals['total_price'] += $expenseItem['value'];

                if ($expenseItem['type'] === 'base_rate') {
                    $totals['total_net'] += $expenseItem['value'];
                }

                // e.g 'tax_and_service_fee' key or any other
                if ($expenseItem['type'] !== 'base_rate') {
                    $totals['total_tax'] += $expenseItem['value'];
                }
            }
        }

        $totals['total_fees'] += (float)($roomPricing['totals']['property_fees']['billable_currency']['value'] ?? 0);

        return $totals;
    }
}
