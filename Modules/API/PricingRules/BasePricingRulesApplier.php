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

    public function __construct(array $requestArray, array $pricingRules)
    {
        $this->requestArray = $requestArray;

        $this->pricingRules = $pricingRules;

        $checkIn = Carbon::parse($requestArray['checkin']);

        $checkOut = Carbon::parse($requestArray['checkout']);

        $this->numberOfNights = $checkIn->diffInDays($checkOut);
    }
}
