<?php

namespace Modules\API\PricingRules;

use Carbon\Carbon;

/**
 *
 */
class BasePricingRulesApplier
{
    /**
     * @var int|null
     */
    protected ?int $supplierId = null;

    /**
     * @var array
     */
    protected array $requestArray = [];

    /**
     * @var array
     */
    protected array $pricingRules = [];

    /**
     * @var int
     */
    protected int $numberOfNights = 0;

    /**
     * @var int
     */
    protected int $numberOfRooms = 0;

    /**
     * @var int
     */
    protected int $totalNumberOfGuests = 0;

    /**
     * @var float|int
     */
    protected float|int $totalPrice = 0;

    /**
     * @var float|int
     */
    protected float|int $totalTax = 0;

    /**
     * @var float|int
     */
    protected float|int $totalFees = 0;
    /**
     * @var float|int
     */
    protected float|int $totalNet = 0;

    /**
     * @var float|int
     */
    protected float|int $markup = 0;

    /**
     * @param array $requestArray
     * @param array $pricingRules
     */
    public function __construct(array $requestArray, array $pricingRules)
    {
        $this->requestArray = $requestArray;

        $this->pricingRules = $pricingRules;

        $checkIn = Carbon::parse($requestArray['checkin']);

        $checkOut = Carbon::parse($requestArray['checkout']);

        $this->numberOfNights = $checkIn->diffInDays($checkOut);

        $this->numberOfRooms = count($requestArray['occupancy']);
    }

    /**
     * @return void
     */
    protected function initPricingRulesProperties(): void
    {
        $this->totalPrice = 0;

        $this->totalTax = 0;

        $this->totalFees = 0;

        $this->totalNet = 0;

        $this->markup = 0;

        $this->totalNumberOfGuests = 0;
    }

    /**
     * @param array $roomTotals
     * @return void
     */
    protected function updateTotals(array $roomTotals): void
    {
        $this->totalPrice += (float)$roomTotals['total_price'];

        $this->totalTax += (float)$roomTotals['total_tax'];

        $this->totalFees += (float)$roomTotals['total_fees'];

        $this->totalNet += (float)$roomTotals['total_net'];
    }

    /**
     * @param int $giataId
     * @param array $conditions
     * @param array $conditionsFieldsToVerify
     * @return bool
     */
    protected function validPricingRule(
        int   $giataId,
        array $conditions,
        array $conditionsFieldsToVerify = ['supplier_id', 'property']
    ): bool
    {
        $validPricingRule = [];

        $conditionsCollection = collect($conditions);

        foreach ($conditionsFieldsToVerify as $field) {
            $filtered = $conditionsCollection->where('field', $field);

            $validPricingRule[$field] = match ($field) {
                'supplier_id' => $filtered->isEmpty() || in_array($this->supplierId, $filtered->pluck('value_from')->all()),
                'property' => $filtered->isEmpty() || in_array($giataId, $filtered->pluck('value_from')->all()),
                'default' => false
            };
        }

        return array_reduce($validPricingRule, function ($carry, $item) {
            return $carry && ($item === true);
        }, true);
    }

    /**
     * @param array $pricingRule
     * @return void
     */
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

    /**
     * @param array $room
     * @return int
     */
    protected function totalNumberOfGuestsInRoom(array $room): int
    {
        return (int)$room['adults'] + (isset($room['children_ages']) ? count($room['children_ages']) : 0);
    }

    /**
     * @param bool $b2b
     * @return array
     */
    protected function totals(bool $b2b = true): array
    {
        $totals = [
            'total_price' => $this->totalPrice,
            'total_tax' => $this->totalTax,
            'total_fees' => $this->totalFees,
            'total_net' => $this->totalNet
        ];

        $markup = round($this->markup, 2);

        $b2b ? $totals['markup'] = $markup : $totals['total_price'] += $markup;

        /**
         * @var array{total_price: float|int,total_tax: float|int,total_fees: float|int,total_net: float|int,markup: float|int} $totals
         */
        return $totals;
    }
}
