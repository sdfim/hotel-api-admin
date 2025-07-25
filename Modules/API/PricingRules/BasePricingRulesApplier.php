<?php

namespace Modules\API\PricingRules;

use Carbon\Carbon;
use Illuminate\Support\Arr;

class BasePricingRulesApplier
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

        $this->totalNet += (float) $roomTotals['total_net'];

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
        foreach ($pricingRules as $pricingRule) {
            $priceValueType = (string) $pricingRule['price_value_type'];
            $fixedValue = (float) $pricingRule['price_value'];
            $manipulablePriceType = (string) $pricingRule['manipulable_price_type'];
            $priceValueTarget = (string) $pricingRule['price_value_target'];
            $totalPropertyName = match ($manipulablePriceType) {
                'net_price' => 'totalNet',
                default => 'totalPrice',
            };

            //Do not include fees to calculate markup!!
            $basePriceForCalculation = $totalPropertyName === 'totalPrice' ? ($this->totalPrice - $this->totalFees) : $this->{$totalPropertyName};
            //$basePriceForCalculation = $this->{$totalPropertyName};
            $percentageValue = ($basePriceForCalculation * $fixedValue) / 100;

            $this->markup += match ($priceValueTarget) {
                'per_person' => match ($priceValueType) {
                    'percentage' => $this->totalNumberOfGuests * $percentageValue,
                    'fixed_value' => $this->totalNumberOfGuests * $fixedValue
                },
                'per_room' => match ($priceValueType) {
                    'percentage' => $percentageValue * $this->numberOfRooms,
                    'fixed_value' => $fixedValue * $this->numberOfRooms
                },
                'per_night' => match ($priceValueType) {
                    'percentage' => $this->numberOfNights * $percentageValue,
                    'fixed_value' => $this->numberOfNights * $fixedValue
                },
                'not_applicable' => match ($priceValueType) {
                    'percentage' => $percentageValue,
                    'fixed_value' => $fixedValue
                },
                'default' => 0
            };
        }
    }

    protected function applyCascadingPricingRulesLogic(array $pricingRules): void
    {
        foreach ($pricingRules as $pricingRule) {
            $priceValueType = (string) $pricingRule['price_value_type'];
            $fixedValue = (float) $pricingRule['price_value'];
            $manipulablePriceType = (string) $pricingRule['manipulable_price_type'];
            $priceValueTarget = (string) $pricingRule['price_value_target'];

            // Determine which total property to use as the base for calculation
            $totalPropertyName = match ($manipulablePriceType) {
                'net_price' => 'totalNet', // Use the initial totalNet for calculation
                default => 'totalPrice', // Use the initial totalPrice for calculation
            };

            // Get the current base value for calculation.
            $currentBaseValueForThisRule = $this->{$totalPropertyName} + $this->markup - ($totalPropertyName === 'totalPrice' ? $this->totalFees : 0);

            $calculatedChange = 0;

            if ($priceValueType === 'percentage') {
                $percentageValue = ($currentBaseValueForThisRule * $fixedValue) / 100;
                $calculatedChange = match ($priceValueTarget) {
                    'per_person' => $this->totalNumberOfGuests * $percentageValue,
                    'per_room' => $percentageValue * $this->numberOfRooms,
                    'per_night' => $this->numberOfNights * $percentageValue,
                    'not_applicable' => $percentageValue,
                    default => 0
                };
            } elseif ($priceValueType === 'fixed_value') {
                $calculatedChange = match ($priceValueTarget) {
                    'per_person' => $this->totalNumberOfGuests * $fixedValue,
                    'per_room' => $fixedValue * $this->numberOfRooms,
                    'per_night' => $this->numberOfNights * $fixedValue,
                    'not_applicable' => $fixedValue,
                    default => 0
                };
            }

            $this->markup += $calculatedChange;
        }
    }

    protected function totalNumberOfGuestsInRoom(array $room): int
    {
        return (int) $room['adults'] + (isset($room['children_ages']) ? count($room['children_ages']) : 0);
    }

    protected function totals(bool $b2b = true): array
    {
        $totals = [
            'total_price' => $this->totalPrice,
            'total_tax' => $this->totalTax,
            'total_fees' => $this->totalFees,
            'total_net' => $this->totalNet,
            'commission_amount' => $this->commissionAmount,
        ];

        $markup = round($this->markup, 2);

        $b2b ? $totals['markup'] = $markup : $totals['total_price'] += $markup;

        /**
         * @var array{total_price: float|int,total_tax: float|int,total_fees: float|int,total_net: float|int,markup: float|int} $totals
         */
        return $totals;
    }
}
