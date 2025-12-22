<?php

namespace Modules\API\PricingRules;

use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Modules\Enums\SupplierNameEnum;

class PricingRulesApplier
{
    protected ?int $supplierId = null;

    protected array $requestArray = [];

    protected array $pricingRules = [];

    protected int $numberOfNights = 0;

    protected int $numberOfRooms = 0;

    protected int $totalNumberOfGuests = 0;

    protected float|int $totalPrice = 0;

    protected float|int $commissionAmount = 0;

    protected float|int $totalTax = 0;

    protected float|int $totalFees = 0;

    protected float|int $totalNet = 0;

    protected float|int $markup = 0;

    public function __construct(array $requestArray, array $pricingRules)
    {
        $this->requestArray = $requestArray;
        $this->pricingRules = $pricingRules;
        $checkIn = Carbon::parse($requestArray['checkin']);
        $checkOut = Carbon::parse($requestArray['checkout']);
        $this->numberOfNights = floor($checkIn->diffInDays($checkOut, true));
        $this->numberOfRooms = count($requestArray['occupancy']);
        $this->supplierId = Supplier::getSupplierId(SupplierNameEnum::HBSI->value);
    }

    protected function initPricingRulesProperties(): void
    {
        $this->totalPrice = 0;
        $this->totalTax = 0;
        $this->totalFees = 0;
        $this->totalNet = 0;
        $this->markup = 0;
        $this->totalNumberOfGuests = 0;
        $this->commissionAmount = 0;
    }

    protected function updateTotals(array $roomTotals): void
    {
        $this->totalPrice += (float) $roomTotals['total_price'];
        $this->totalTax += (float) $roomTotals['total_tax'];
        $this->totalFees += (float) $roomTotals['total_fees'];
        $this->totalNet += (float) $roomTotals['total_price'];
        $this->commissionAmount = (float) Arr::get($roomTotals, 'commission_amount', 0);
    }

    protected function validPricingRule(
        int $giataId,
        array $conditions,
        string $roomName,
        string|int $roomCode,
        string|int $roomType,
        string|int $rateCode,
        string|int $srRoomId,
        array $conditionsFieldsToVerify = ['supplier_id', 'property'],
        float $roomTotalsPrice = 0
    ): bool {
        $validPricingRule = [
            'supplier_id' => true,
            'property' => true,
            'room_name' => true,
            'room_code' => true,
            'room_type' => true,
            'total_price' => true,
            'rate_code' => true,
            'room_type_cr' => true,
        ];

        foreach ($conditionsFieldsToVerify as $field) {
            foreach ($conditions as $condition) {
                if ($condition['field'] !== $field) {
                    continue;
                }
                $validPricingRule[$field] = match ($field) {
                    'supplier_id' => $this->evaluateCondition($condition, $this->supplierId),
                    'property' => $this->evaluateCondition($condition, $giataId),
                    'room_name' => $this->evaluateCondition($condition, $roomName),
                    'room_code' => $this->evaluateCondition($condition, $roomCode),
                    'room_type' => $this->evaluateCondition($condition, $roomType),
                    'rate_code' => $this->evaluateCondition($condition, $rateCode),
                    'total_price' => $this->evaluateCondition($condition, $roomTotalsPrice),
                    'room_type_cr' => $this->evaluateCondition($condition, $srRoomId),
                    default => true
                };
            }
        }

        if (array_filter($validPricingRule) === []) {
            return true;
        }

        return array_reduce($validPricingRule, fn ($carry, $item) => $carry && $item, true);
    }

    private function evaluateCondition(array $condition, $value): bool
    {
        $compare = $condition['compare'];
        $valueFrom = $condition['value_from'];
        $valueTo = $condition['value_to'];
        $valueArr = $condition['value'];
        $valueArr = is_array($valueArr) ? $valueArr : preg_split('/;\s*/', $valueArr);

        if ($condition['field'] === 'room_type_cr') {
            logger()->debug('Evaluating condition', [
                'field' => $condition['field'],
                'compare' => $compare,
                'value_from' => $valueFrom,
                'value_to' => $valueTo,
                'value' => $value,
                'valueArr' => $valueArr,
            ]);
        }

        return match ($compare) {
            '=' => (string) $valueFrom === (string) $value,
            '!=' => (string) $valueFrom !== (string) $value,
            'in' => in_array($value, $valueArr),
            'not_in' => ! in_array($value, $valueArr),
            '>' => (float) $valueFrom < (float) $value,
            '<' => (float) $valueTo > (float) $value,
            '>=' => (float) $valueFrom <= (float) $value,
            '<=' => (float) $valueTo >= (float) $value,
            'between' => (float) $valueFrom < (float) $value && (float) $valueTo > (float) $value,
            default => false
        };
    }

    protected function applyParallelPricingRulesLogic(array $pricingRules): void
    {
        // Clean base excludes all taxes/fees since they live in total_tax
        $basePriceForCalculation = $this->totalPrice - $this->totalTax;

        foreach ($pricingRules as $pricingRule) {
            $priceValueType = (string) $pricingRule['price_value_type'];        // 'percentage' | 'fixed_value'
            $fixedValue = (float) $pricingRule['price_value'];
            $priceValueTarget = (string) $pricingRule['price_value_target'];    // 'per_person'|'per_room'|'per_night'|'not_applicable'

            // Percentage is always computed off the clean base
            $percentageValue = ($basePriceForCalculation * $fixedValue) / 100;

            $this->markup += match ($priceValueTarget) {
                'per_person' => match ($priceValueType) {
                    'percentage' => $this->totalNumberOfGuests * $percentageValue,
                    'fixed_value' => $this->totalNumberOfGuests * $fixedValue,
                },
                'per_room' => match ($priceValueType) {
                    'percentage' => $percentageValue * $this->numberOfRooms,
                    'fixed_value' => $fixedValue * $this->numberOfRooms,
                },
                'per_night' => match ($priceValueType) {
                    'percentage' => $this->numberOfNights * $percentageValue,
                    'fixed_value' => $this->numberOfNights * $fixedValue,
                },
                'not_applicable' => match ($priceValueType) {
                    'percentage' => $percentageValue,
                    'fixed_value' => $fixedValue,
                },
                default => 0,
            };
        }
    }

    protected function applyCascadingPricingRulesLogic(array $pricingRules): void
    {
        foreach ($pricingRules as $pricingRule) {
            $priceValueType = (string) $pricingRule['price_value_type'];       // 'percentage' | 'fixed_value'
            $fixedValue = (float) $pricingRule['price_value'];
            $priceValueTarget = (string) $pricingRule['price_value_target'];   // 'per_person'|'per_room'|'per_night'|'not_applicable'

            // Clean base excludes all taxes/fees since they live in total_tax
            $cleanBase = $this->totalPrice - $this->totalTax;

            // In cascading mode we accumulate over (clean base + markup so far)
            $currentBaseValueForThisRule = $cleanBase + $this->markup;

            if ($priceValueType === 'percentage') {
                $percentageValue = ($currentBaseValueForThisRule * $fixedValue) / 100;

                $calculatedChange = match ($priceValueTarget) {
                    'per_person' => $this->totalNumberOfGuests * $percentageValue,
                    'per_room' => $percentageValue * $this->numberOfRooms,
                    'per_night' => $this->numberOfNights * $percentageValue,
                    'not_applicable' => $percentageValue,
                    default => 0,
                };
            } else { // fixed_value
                $calculatedChange = match ($priceValueTarget) {
                    'per_person' => $this->totalNumberOfGuests * $fixedValue,
                    'per_room' => $fixedValue * $this->numberOfRooms,
                    'per_night' => $this->numberOfNights * $fixedValue,
                    'not_applicable' => $fixedValue,
                    default => 0,
                };
            }

            $this->markup += $calculatedChange;
        }
    }

    protected function totals(): array
    {
        // Base totals coming from supplier (without our markup)
        $totals = [
            'total_price' => (float) $this->totalPrice, // base = total_net + total_tax + total_fees
            'total_tax' => (float) $this->totalTax,
            'total_fees' => (float) $this->totalFees,
            'total_net' => (float) $this->totalNet,
            'commission_amount' => (float) $this->commissionAmount,
        ];

        // The value previously returned in "markup" (our rule-based markup)
        $rulesMarkup = round((float) $this->markup, 2);
        $totals['total_price'] = round($totals['total_price'] + $rulesMarkup, 2);

        return $totals;
    }

    /**
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
            $inclusiveTax = 0.0;

            $baseRatePerNight = (float) Arr::get($rate, 'amount_before_tax', 0.0);
            $totals['total_net'] += $baseRatePerNight;

            foreach (Arr::get($rate, 'taxes', []) as $tax) {
                $collectedBy = strtolower((string) Arr::get($tax, 'collected_by'));
                if ($collectedBy === 'direct') {
                    continue;
                }

                $amount = (float) Arr::get($tax, 'amount', 0.0);
                if ($amount == 0 && isset($tax['displayable_amount'])) {
                    $amount = (float) $tax['displayable_amount'];
                }

                $totals['total_tax'] += $amount;
                if (Arr::get($tax, 'type') === 'Inclusive') {
                    $inclusiveTax += $amount;
                }
            }

            $amountBeforeTax = (float) Arr::get($rate, 'amount_before_tax', 0.0);
            $amountAfterTax = (float) Arr::get($rate, 'amount_after_tax', 0.0);

            if ($amountBeforeTax == $amountAfterTax && $inclusiveTax > 0) {
                $totals['total_net'] -= $inclusiveTax;
            }
        }

        if (! empty($transformedRoomPricing)) {
            $fees = Arr::collapse(Arr::pluck($transformedRoomPricing, 'fees'));

            foreach ($fees as $fee) {
                if (Arr::get($fee, 'type') !== 'PropertyCollects') {
                    $feeAmount = (float) Arr::get($fee, 'amount', 0.0);
                    if ($feeAmount == 0.0 && isset($fee['displayable_amount'])) {
                        $feeAmount = (float) $fee['displayable_amount'];
                    }

                    if (strtolower((string) Arr::get($fee, 'collected_by')) !== 'direct') {
                        $totals['total_fees'] += $feeAmount;
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
