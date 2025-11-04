<?php

namespace Modules\API\PricingRules\HBSI;

use App\Models\Supplier;
use Illuminate\Support\Arr;
use Modules\API\PricingRules\BasePricingRulesApplier;
use Modules\API\PricingRules\PricingRulesApplierInterface;
use Modules\Enums\SupplierNameEnum;

class HbsiPricingRulesApplier extends BasePricingRulesApplier implements PricingRulesApplierInterface
{
    public function __construct(array $requestArray, array $pricingRules)
    {
        parent::__construct($requestArray, $pricingRules);

        $this->supplierId = Supplier::getSupplierId(SupplierNameEnum::HBSI->value);
    }

    /**
     * @param array{
     *     Rates: array,
     *     rateOccupancy: string
     *  } $roomsPricingArray
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

        // $roomsPricingArray['rateOccupancy'] is a string value in the following format:
        // 'number_of_adults-number_of_children-number_of_babies'. For example: '2-1-1'.
        // If there are no children or babies, then the format will appear as: '2-0-0'.
        $this->totalNumberOfGuests = array_sum(explode('-', $roomsPricingArray['rateOccupancy']));

        $roomTotals = $this->calculateRoomTotals($roomsPricingArray['Rates']);

        $this->updateTotals($roomTotals);

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
                ['supplier_id', 'property', 'room_name', 'room_type', 'total_price', 'rate_code', 'room_type_cr'],
                $roomTotals['total_price'],
            ];
            if ($this->validPricingRule(...$params)) {
                $validPricingRules[] = $pricingRule;
            }
        }

        if (! empty($validPricingRules)) {
            usort($validPricingRules, fn ($a, $b) => $b['weight'] <=> $a['weight']);

            // Get the pricing rule application strategy from config
            $strategy = config('pricing-rules.application_strategy');

            if ($strategy === 'cascading') {
                $this->applyCascadingPricingRulesLogic($validPricingRules);
            } else {
                $this->applyParallelPricingRulesLogic($validPricingRules);
            }
        }

        $result = $this->totals();
        $result['validPricingRules'] = $validPricingRules;

        return $result;
    }

    private function calculateTransformedRoomTotals(array $transformedRoomPricing): array
    {
        /**
         * @var array{total_price: float|int,total_tax: float|int,total_fees: float|int,total_net: float|int} $totals
         */
        $totals = [
            'total_price' => 0,
            'total_tax' => 0,
            'total_fees' => 0,
            'total_net' => 0,
        ];

        foreach ($transformedRoomPricing as $rate) {
            $totals['total_net'] += (float) $rate['TotalAmountBeforeTax'];

            $unitMultiplier = (int) $rate['UnitMultiplier'];

            foreach ($rate['Taxes'] as $tax) {
                $totals['total_tax'] += (float) $tax['Amount'] * $unitMultiplier;
            }
        }

        foreach (Arr::get(Arr::get($transformedRoomPricing, 0, []), 'Fees', []) as $fee) {
            $feeMultiplier = Arr::get($fee, 'MultiplierFee', 1);
            $totals['total_fees'] += (float) $fee['Amount'] * $feeMultiplier;
        }

        $totals['total_price'] += $totals['total_net'] + $totals['total_fees'] + $totals['total_tax'];

        return $totals;
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
        ];

        if (array_key_first($roomPricing['Rate']) !== 0) {
            $roomPricingLoop[] = $roomPricing['Rate'];
        } else {
            $roomPricingLoop = $roomPricing['Rate'];
        }

        foreach ($roomPricingLoop as $rate) {
            // check if AmountBeforeTax is equal to AmountAfterTax
            $current_total_net = (float) $rate['Total']['@attributes']['AmountBeforeTax'];
            $current_total_price = (float) $rate['Total']['@attributes']['AmountAfterTax'];

            $totals['total_net'] += $current_total_net;

            if ($current_total_net == $current_total_price) {
                continue;
            }

            if (isset($rate['Base']['Taxes']['Tax'])) {
                $unitMultiplier = (int) $rate['@attributes']['UnitMultiplier'];
                if (array_key_first($rate['Base']['Taxes']['Tax']) === 0) {
                    foreach ($rate['Base']['Taxes']['Tax'] as $tax) {
                        $totals = $this->calculateTaxAndFees($tax, $totals, $unitMultiplier);
                    }
                } else {
                    $tax = $rate['Base']['Taxes']['Tax'];
                    $totals = $this->calculateTaxAndFees($tax, $totals, $unitMultiplier);
                }
            }
        }

        $totals['total_price'] += $totals['total_net'] + $totals['total_fees'] + $totals['total_tax'];

        return $totals;
    }

    /**
     * @return int[]
     */
    private function calculateTaxAndFees($tax, $totals, $unitMultiplier): array
    {
        /*
        $code = $tax['@attributes']['Code'];
        $taxText = strtolower(Arr::get($tax, 'TaxDescription.Text'));

        if (in_array($taxText, $this->fees)) {
            $totals['total_fees'] += (float) $tax['@attributes']['Amount'] * $unitMultiplier;
            \Log::info('total_fees '.$taxText, ['$tax' => $tax]);
        } elseif (in_array($taxText, $this->taxes)) {
            $totals['total_tax'] += (float) $tax['@attributes']['Amount'] * $unitMultiplier;
            \Log::info('total_tax 1 '.$taxText, ['$tax' => $tax]);
        } else {
            $totals['total_tax'] += (float) $tax['@attributes']['Amount'] * $unitMultiplier;
            \Log::info('total_tax 2 '.$taxText, ['$tax' => $tax]);
        }
        */

        if ($tax['@attributes']['Type'] === 'PropertyCollects') {
            $totals['total_fees'] += (float) $tax['@attributes']['Amount'];
        } else {
            // TODO: check that logic when there are actual lists of taxes and fees.
            $totals['total_tax'] += (float) $tax['@attributes']['Amount'] * $unitMultiplier;
        }

        return $totals;
    }
}
