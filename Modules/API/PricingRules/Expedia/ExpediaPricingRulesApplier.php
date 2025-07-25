<?php

namespace Modules\API\PricingRules\Expedia;

use App\Models\Supplier;
use Modules\API\PricingRules\BasePricingRulesApplier;
use Modules\API\PricingRules\PricingRulesApplierInterface;

class ExpediaPricingRulesApplier extends BasePricingRulesApplier implements PricingRulesApplierInterface
{
    public function __construct(array $requestArray, array $pricingRules)
    {
        parent::__construct($requestArray, $pricingRules);

        $this->supplierId = Supplier::getSupplierId('Expedia');
    }

    /**l
     * @return array{
     *      total_price: float|int,
     *      total_tax: float|int,
     *      total_fees: float|int,
     *      total_net: float|int,
     *      markup: float|int
     *  }
     */
    public function apply(
        int $giataId,
        array $roomsPricingArray,
        string $roomName,
        string|int $roomCode,
        string|int $roomType,
        string|int $rateCode,
        string|int $srRoomId,
        bool $b2b = true
    ): array {
        $this->initPricingRulesProperties();

        $roomTotals = [];

        foreach ($this->requestArray['occupancy'] as $room) {
            $this->totalNumberOfGuests += $this->totalNumberOfGuestsInRoom($room);

            $roomsPricingKey = isset($room['children_ages']) ? $room['adults'].'-'.implode(',', $room['children_ages']) : $room['adults'];

            $roomTotals = $this->calculateRoomTotals($roomsPricingArray[$roomsPricingKey]);

            $this->updateTotals($roomTotals);
        }

        $validPricingRules = [];

        foreach ($this->pricingRules as $pricingRule) {
            $params = [
                $giataId,
                $pricingRule['conditions'],
                $roomName,
                $roomCode,
                $roomType,
                $rateCode,
                $srRoomId,
                ['supplier_id', 'property', 'room_name', 'room_code', 'room_type', 'total_price', 'rate_code', 'room_type_cr'],
                $roomTotals['total_price'],
            ];
            if ($this->validPricingRule(...$params)) {
                $validPricingRules[] = $pricingRule;
            }
        }

        // Get the pricing rule application strategy from config
        $strategy = config('pricing-rules.application_strategy');

        if ($strategy === 'cascading') {
            $this->applyCascadingPricingRulesLogic($validPricingRules);
        } else {
            $this->applyParallelPricingRulesLogic($validPricingRules);
        }

        $result = $this->totals($b2b);
        $result['validPricingRules'] = $validPricingRules;

        return $result;
    }

    /**
     * Calculates total_price(net_price, fees, taxes)
     *
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
            'total_net' => 0,
            'commission_amount' => 0,
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

        if (isset($roomPricing['stay'])) {
            foreach ($roomPricing['stay'] as $expenseItem) {
                $totals['total_price'] += $expenseItem['value'];
            }
        }

        $totals['total_fees'] += (float) ($roomPricing['totals']['property_fees']['billable_currency']['value'] ?? 0);
        $totals['commission_amount'] += (float) ($roomPricing['totals']['marketing_fee']['billable_currency']['value'] ?? 0);

        return $totals;
    }
}
