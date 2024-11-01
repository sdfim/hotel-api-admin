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
        string|int $roomType,
        array $conditionsFieldsToVerify = ['supplier_id', 'property'],
        bool  $useAndCondition = false
    ): bool
    {
        $validPricingRule = [
            'supplier_id' => [],
            'property' => [],
            'room_name' => [],
            'room_code' => [],
            'room_type' => [],
        ];

        if ($roomType === 'A5V') {
            $info = $roomType;
        }

        $conditionsCollection = collect($conditions);

        foreach ($conditionsFieldsToVerify as $field) {
            $filtered = $conditionsCollection->where('field', $field);

            foreach ($filtered as $condition) {
                $validPricingRule[$field][] = match ($field) {
                    'supplier_id' => (string)$condition['value_from'] === (string)$this->supplierId,
                    'property' => (string)$condition['value_from'] === (string)$giataId,
                    'room_name' => (string)$condition['value_from'] === (string)$roomName,
                    'room_code' => (string)$condition['value_from'] === (string)$roomCode,
                    'room_type' => (string)$condition['value_from'] === (string)$roomType,
                    default => false
                };
            }

//            if ($filtered->isEmpty()) {
//                $validPricingRule[$field][] = false;
//            }
        }



        if ($validPricingRule['supplier_id'] === []
            && $validPricingRule['property'] === []
            && $validPricingRule['room_name'] === []
            && $validPricingRule['room_code'] === []
            && $validPricingRule['room_type'] === []
        ) {
            return true;
        }

        if ($useAndCondition) {
            foreach ($validPricingRule as $results) {
                if (!in_array(true, $results, true)) {
                    return false;
                }
            }
            return true;
        } else {
            foreach ($validPricingRule as $results) {
                if (in_array(true, $results, true)) {
                    return true;
                }
            }
            return false;
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
