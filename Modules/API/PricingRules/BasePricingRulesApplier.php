<?php

namespace Modules\API\PricingRules;

use Carbon\Carbon;

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
     * @var int
     */
    protected int $numberOfNights;

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
    protected float|int $priceValue;

    /**
     * @var string
     */
    protected string $manipulablePriceType;

    /**
     * @var string
     */
    protected string $priceValueTarget;

    /**
     * @var int
     */
    protected int $totalNumberOfGuestsInRoom;

    /**
     * @var array
     */
    protected array $roomTotals = [];

    /**
     * @var float|int
     */
    protected float|int $priceValueFromTotal = 0;

    /**
     * @var float|int
     */
    protected float|int $priceValueFromTotalNet = 0;

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
    protected function applyPricingRulesLogic(): void
    {
        // these values are calculated in the same way for all cases below, therefore they are moved to the top from each closure
        $this->totalTax += $this->roomTotals['total_tax'];

        $this->totalFees += $this->roomTotals['total_fees'];

        if ($this->validPricingRule) {
            // calculate pricing for each room from request
            $affiliateServiceCharge = 0;

            if ($this->manipulablePriceType === 'total_price') {
                $this->priceValueFromTotal = ($this->roomTotals['total_price'] * $this->priceValue) / 100;

                $affiliateServiceCharge = match ($this->priceValueTarget) {
                    'per_guest' => match ($this->priceValueType) {
                        'percentage' => $this->roomTotals['total_price'] + ($this->totalNumberOfGuestsInRoom * $this->priceValueFromTotal),
                        'fixed_value' => $this->roomTotals['total_price'] + ($this->totalNumberOfGuestsInRoom * $this->priceValue)
                    },
                    'per_room' => match ($this->priceValueType) {
                        'percentage' => $this->roomTotals['total_price'] + $this->priceValueFromTotal,
                        'fixed_value' => $this->roomTotals['total_price'] + $this->priceValue
                    },
                    'per_night' => match ($this->priceValueType) {
                        'percentage' => $this->roomTotals['total_price'] + ($this->numberOfNights * $this->priceValueFromTotal),
                        'fixed_value' => $this->roomTotals['total_price'] + ($this->numberOfNights * $this->priceValue)
                    }
                };

                $this->totalNet += $this->roomTotals['total_net'];
            }

            // in case when supplier is Expedia/HBSI total_price and rate_price should be calculated the same way
            if ($this->manipulablePriceType === 'net_price' || $this->manipulablePriceType === 'rate_price') {
                $this->priceValueFromTotalNet = ($this->roomTotals['total_net'] * $this->priceValue) / 100;

                $affiliateServiceCharge = match ($this->priceValueTarget) {
                    'per_guest' => match ($this->priceValueType) {
                        'percentage' => $this->totalNumberOfGuestsInRoom * $this->priceValueFromTotalNet,
                        'fixed_value' => $this->totalNumberOfGuestsInRoom * $this->priceValue
                    },
                    'per_room' => match ($this->priceValueType) {
                        'percentage' => $this->priceValueFromTotalNet,
                        'fixed_value' => $this->priceValue
                    },
                    'per_night' => match ($this->priceValueType) {
                        'percentage' => $this->numberOfNights * $this->priceValueFromTotalNet,
                        'fixed_value' => $this->numberOfNights * $this->priceValue,
                    },
                    'default' => 0
                };

                $this->totalNet += $this->roomTotals['total_net'];
            }

            // these values are calculated in the same way for all $manipulablePriceType
            $this->affiliateServiceCharge += $affiliateServiceCharge;

            $this->totalPrice += $this->roomTotals['total_price'];
        } else {
            $this->totalPrice += $this->roomTotals['total_price'];

            $this->totalNet += $this->roomTotals['total_net'];
        }
    }

    /**
     * @param array $room
     * @return int
     */
    protected function totalNumberOfGuestsInRoom(array $room): int
    {
        return (int)$room['adults'] + ($room['children_ages'] ? count($room['children_ages']) : 0);
    }

    /**
     * @param bool $b2b
     * @return array
     */
    protected function totals(bool $b2b = true): array
    {
        $this->affiliateServiceCharge = $b2b ? round($this->affiliateServiceCharge, 2) : 0.00;

        /**
         * @var array{total_price: float|int,total_tax: float|int,total_fees: float|int,total_net: float|int,affiliate_service_charge: float|int}
         */
        return [
            'total_price' => $this->totalPrice,
            'total_tax' => $this->totalTax,
            'total_fees' => $this->totalFees,
            'total_net' => $this->totalNet,
            'affiliate_service_charge' => $this->affiliateServiceCharge
        ];
    }
}
