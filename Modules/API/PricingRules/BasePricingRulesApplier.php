<?php

namespace Modules\API\PricingRules;

use Carbon\Carbon;

class BasePricingRulesApplier
{
    protected ?int $supplierId = null;

    protected array $requestArray = [];

    protected array $pricingRules = [];

    protected int $numberOfNights = 0;

    protected int $numberOfRooms = 0;

    protected int $totalNumberOfGuests = 0;

    protected float|int $totalPrice = 0;

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
    }

    protected function updateTotals(array $roomTotals): void
    {
        $this->totalPrice += (float)$roomTotals['total_price'];

        $this->totalTax += (float)$roomTotals['total_tax'];

        $this->totalFees += (float)$roomTotals['total_fees'];

        $this->totalNet += (float)$roomTotals['total_net'];
    }

    protected function validPricingRule(
        int        $giataId,
        array      $conditions,
        string     $roomName,
        string|int $roomCode,
        array $conditionsFieldsToVerify = ['supplier_id', 'property'],
        bool  $useAndCondition = false  // Use AND condition if true, OR condition if false
    ): bool
    {
        // Initialize array to store results for each condition type
        $validPricingRule = [
            'supplier_id' => [],
            'property' => [],
            'room_name' => [],
            'room_code' => [],
        ];

        $conditionsCollection = collect($conditions);

        // Evaluate each condition for the specified fields
        foreach ($conditionsFieldsToVerify as $field) {
            $filtered = $conditionsCollection->where('field', $field);

            foreach ($filtered as $condition) {
                // Add results based on field-specific comparison
                $validPricingRule[$field][] = match ($field) {
                    'supplier_id' => $this->supplierId === $condition['value_from'],
                    'property' => $giataId === $condition['value_from'],
                    'room_name' => $roomName === $condition['value_from'],
                    'room_code' => $roomCode === $condition['value_from'],
                    default => false
                };
            }
        }

        if ($useAndCondition) {
            // AND Condition: Each group must have at least one true value
            foreach ($validPricingRule as $results) {
                if (!in_array(true, $results, true)) {
                    return false; // Return false if any group has no true values
                }
            }
            return true; // All groups have at least one true value
        } else {
            // OR Condition: Return true if at least one true condition exists across all groups
            foreach ($validPricingRule as $results) {
                if (in_array(true, $results, true)) {
                    return true; // Return true if any true condition is found
                }
            }
            return false; // Return false if no true conditions were found in any group
        }
    }

    protected function applyPricingRulesLogic(array $pricingRule): void
    {
        $priceValueType = (string)$pricingRule['price_value_type'];
        $fixedValue = (float)$pricingRule['price_value'];
        $manipulablePriceType = (string)$pricingRule['manipulable_price_type'];
        $priceValueTarget = (string)$pricingRule['price_value_target'];
        $totalPropertyName = match ($manipulablePriceType) {
            'net_price' => 'totalNet',
            default => 'totalPrice',
        };

        $percentageValue = ($this->{$totalPropertyName} * $fixedValue) / 100;

        $this->markup += match ($priceValueTarget) {
            'per_guest' => match ($priceValueType) {
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

    protected function totalNumberOfGuestsInRoom(array $room): int
    {
        return (int)$room['adults'] + (isset($room['children_ages']) ? count($room['children_ages']) : 0);
    }

    protected function totals(bool $b2b = true): array
    {
        $totals = [
            'total_price' => $this->totalPrice,
            'total_tax' => $this->totalTax,
            'total_fees' => $this->totalFees,
            'total_net' => $this->totalNet,
        ];

        $markup = round($this->markup, 2);

        $b2b ? $totals['markup'] = $markup : $totals['total_price'] += $markup;

        /**
         * @var array{total_price: float|int,total_tax: float|int,total_fees: float|int,total_net: float|int,markup: float|int} $totals
         */
        return $totals;
    }
}
