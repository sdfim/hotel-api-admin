<?php

namespace Modules\API\PricingRules;

use App\Models\Supplier;
use Illuminate\Support\Arr;
use Modules\Enums\SupplierNameEnum;

class PricingRulesApplier extends BasePricingRulesApplier
{
    public function __construct(array $requestArray, array $pricingRules)
    {
        parent::__construct($requestArray, $pricingRules);

        $this->supplierId = Supplier::getSupplierId(SupplierNameEnum::HBSI->value);
    }

    /**
     * @param int $giataId
     * @param array $transformedRates
     * @param string $rateOccupancy
     * @param string $roomName
     * @param string|int $roomCode
     * @param string|int $roomType
     * @param string|int $rateCode
     * @param string|int $srRoomId
     * @param bool $b2b
     * @return array{
     *      total_price: float|int,
     *      total_tax: float|int,
     *      total_fees: float|int,
     *      total_net: float|int,
     *      commission_amount: float|int,
     *      validPricingRules?: array
     *  }
     */
    public function apply(
        int $giataId,
        array $transformedRates,
        string $rateOccupancy,
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
        $this->totalNumberOfGuests = array_sum(explode('-', $rateOccupancy));

        $roomTotals = $this->calculateRoomTotals($transformedRates);
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

    private function calculateRoomTotals(array $transformedRoomPricing): array
    {
        /**
         * @var array{total_price: float|int,total_tax: float|int,total_fees: float|int,total_net: float|int} $totals
         */
        $totals = [
            'total_price' => 0.0,
            'total_tax' => 0.0,
            'total_fees' => 0.0,
            'total_net' => 0.0,
        ];

        foreach ($transformedRoomPricing as $rate) {
            $totalInclusiveTax = 0.0;
            $unitMultiplier = (int) Arr::get($rate, 'unit_multiplier', 1);

            $baseRatePerNight = (float) Arr::get($rate, 'amount_before_tax', 0.0);
            $totals['total_net'] += $baseRatePerNight * $unitMultiplier;

            foreach (Arr::get($rate, 'taxes', []) as $tax) {
                $collectedBy = strtolower((string) Arr::get($tax, 'collected_by'));
                if ($collectedBy === 'direct') {
                    continue;
                }

                $amount = (float) Arr::get($tax, 'amount', 0.0);
                if ($amount == 0 && isset($tax['displayable_amount'])) {
                    $amount = (float) $tax['displayable_amount'];
                }

                $totals['total_tax'] += $amount * $unitMultiplier;
                if (Arr::get($tax, 'type') === 'Inclusive') {
                    $totalInclusiveTax += $amount * $unitMultiplier;
                }
            }

            $totalAmountBeforeTax = (float) Arr::get($rate, 'total_amount_before_tax', 0.0);
            $totalAmountAfterTax = (float) Arr::get($rate, 'total_amount_after_tax', 0.0);

            if ($totalAmountBeforeTax == $totalAmountAfterTax && $totalInclusiveTax > 0) {
                $totals['total_net'] -= $totalInclusiveTax;
            }
        }

        if (! empty($transformedRoomPricing)) {
            $fees = Arr::collapse(Arr::pluck($transformedRoomPricing, 'fees'));
            $uniqueFees = array_values(array_unique($fees, SORT_REGULAR));

            foreach ($uniqueFees as $fee) {
                $feeMultiplier = (float) Arr::get($fee, 'multiplier_fee', 1.0);

                if (Arr::get($fee, 'type') !== 'PropertyCollects') {
                    $feeAmount = (float) Arr::get($fee, 'amount', 0.0);
                    if ($feeAmount == 0.0 && isset($fee['displayable_amount'])) {
                        $feeAmount = (float) $fee['displayable_amount'];
                    }

                    if (strtolower((string) Arr::get($fee, 'collected_by')) !== 'direct') {
                        $totals['total_fees'] += $feeAmount * $feeMultiplier;
                    }
                }
            }
        }

        $totals['total_price'] = $totals['total_net'] + $totals['total_fees'] + $totals['total_tax'];

        $totals['total_price'] = round($totals['total_price'], 2);
        $totals['total_tax'] = round($totals['total_tax'], 2);
        $totals['total_fees'] = round($totals['total_fees'], 2);
        $totals['total_net'] = round($totals['total_net'], 2);

        return $totals;
    }
}
