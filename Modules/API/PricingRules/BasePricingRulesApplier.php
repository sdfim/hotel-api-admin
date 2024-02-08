<?php

namespace Modules\API\PricingRules;

use Carbon\Carbon;

/**
 *
 */
class BasePricingRulesApplier
{
    /**
     * @var array
     */
    protected array $requestArray;

    /**
     * @var array
     */
    protected array $pricingRules;

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
    protected float|int $affiliateServiceCharge = 0;

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
     * @var bool
     */
    protected bool $validPricingRule;

    /**
     * @var string
     */
    protected string $priceValueType;

    /**
     * @var float|int
     */
    protected float|int $priceValue = 0;

    /**
     * @var string
     */
    protected string $manipulablePriceType;

    /**
     * @var string
     */
    protected string $priceValueTarget;

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
     * @param array $pricingRule
     * @return void
     */
    protected function setPricingRuleValues(array $pricingRule): void
    {
        $this->priceValueType = (string)$pricingRule['price_value_type'];

        $this->priceValue = (float)$pricingRule['price_value'];

        $this->manipulablePriceType = (string)$pricingRule['manipulable_price_type'];

        $this->priceValueTarget = (string)$pricingRule['price_value_target'];
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

        $this->affiliateServiceCharge = 0;

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
     * @return void
     */
    protected function applyPricingRulesLogic(): void
    {
        // calculate pricing for each room from request
        $this->affiliateServiceCharge = 0;

        if ($this->manipulablePriceType === 'total_price') {
            $priceValueFromTotal = ($this->totalPrice * $this->priceValue) / 100;

            $this->affiliateServiceCharge += match ($this->priceValueTarget) {
                'per_guest' => match ($this->priceValueType) {
                    'percentage' => $this->totalNumberOfGuests * $priceValueFromTotal,
                    'fixed_value' => $this->totalNumberOfGuests * $this->priceValue
                },
                'per_room' => match ($this->priceValueType) {
                    'percentage' => $priceValueFromTotal * $this->numberOfRooms,
                    'fixed_value' => $this->priceValue * $this->numberOfRooms
                },
                'per_night' => match ($this->priceValueType) {
                    'percentage' => $this->numberOfNights * $priceValueFromTotal,
                    'fixed_value' => $this->numberOfNights * $this->priceValue
                }
            };
        }

        // in case when supplier is Expedia/HBSI total_price and rate_price should be calculated the same way
        if ($this->manipulablePriceType === 'net_price' || $this->manipulablePriceType === 'rate_price') {
            $priceValueFromTotalNet = ($this->totalNet * $this->priceValue) / 100;

            $this->affiliateServiceCharge += match ($this->priceValueTarget) {
                'per_guest' => match ($this->priceValueType) {
                    'percentage' => $this->totalNumberOfGuests * $priceValueFromTotalNet,
                    'fixed_value' => $this->totalNumberOfGuests * $this->priceValue
                },
                'per_room' => match ($this->priceValueType) {
                    'percentage' => $priceValueFromTotalNet * $this->numberOfRooms,
                    'fixed_value' => $this->priceValue * $this->numberOfRooms
                },
                'per_night' => match ($this->priceValueType) {
                    'percentage' => $this->numberOfNights * $priceValueFromTotalNet,
                    'fixed_value' => $this->numberOfNights * $this->priceValue
                },
                'default' => 0
            };
        }
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

        $affiliateServiceCharge = round($this->affiliateServiceCharge, 2);

        $b2b ? $totals['affiliate_service_charge'] = $affiliateServiceCharge : $totals['total_price'] += $affiliateServiceCharge;

        /**
         * @var array{total_price: float|int,total_tax: float|int,total_fees: float|int,total_net: float|int,affiliate_service_charge: float|int} $totals
         */
        return $totals;
    }
}
