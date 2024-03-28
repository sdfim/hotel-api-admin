<?php

namespace Modules\API\PricingAPI\ResponseModels;

class RoomGroupsResponse extends BaseResponse
{
    /**
     * @var float
     */
    private float $total_price;
    /**
     * @var float
     */
    private float $total_tax;
    /**
     * @var float
     */
    private float $total_fees;
    /**
     * @var float
     */
    private float $total_net;
    /**
     * @var float
     */
    private float $affiliate_service_charge;
    /**
     * @var string
     */
    private string $currency;
    /**
     * @var bool
     */
    private bool $pay_now;
    /**
     * @var bool
     */
    private bool $pay_at_hotel;
    /**
     * @var bool
     */
    private bool $non_refundable;
    /**
     * @var string
     */
    private string $meal_plan;
    /**
     * @var int
     */
    private int $rate_id;
    /**
     * @var string
     */
    private string $rate_description;
    /**
     * @var array
     */
    private array $cancellation_policies;
    /**
     * @var bool
     */
    private bool $opaque;
    /**
     * @var array
     */
    private array $rooms;

    /**
     * @return float
     */
    private function getAffiliateServiceCharge(): float
    {
        return $this->affiliate_service_charge;
    }

    /**
     * @param float $affiliate_service_charge
     * @return void
     */
    public function setAffiliateServiceCharge(float $affiliate_service_charge): void
    {
        $this->affiliate_service_charge = $affiliate_service_charge;
    }

    public function getTotalPrice(): float
    {
        return $this->total_price;
    }

    /**
     * @param float $total_price
     * @return void
     */
    public function setTotalPrice(float $total_price): void
    {
        $this->total_price = $total_price;
    }

    /**
     * @param float $total_tax
     * @return void
     */
    public function setTotalTax(float $total_tax): void
    {
        $this->total_tax = $total_tax;
    }

    /**
     * @return float
     */
    public function getTotalTax(): float
    {
        return $this->total_tax;
    }

    /**
     * @param float $total_fees
     * @return void
     */
    public function setTotalFees(float $total_fees): void
    {
        $this->total_fees = $total_fees;
    }

    /**
     * @return float
     */
    public function getTotalFees(): float
    {
        return $this->total_fees;
    }

    /**
     * @param float $total_net
     * @return void
     */
    public function setTotalNet(float $total_net): void
    {
        $this->total_net = $total_net;
    }

    /**
     * @return float
     */
    public function getTotalNet(): float
    {
        return $this->total_net;
    }

    /**
     * @param string $currency
     * @return void
     */
    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param bool $pay_now
     * @return void
     */
    public function setPayNow(bool $pay_now): void
    {
        $this->pay_now = $pay_now;
    }

    /**
     * @return bool
     */
    public function getPayNow(): bool
    {
        return $this->pay_now;
    }

    /**
     * @param bool $pay_at_hotel
     * @return void
     */
    public function setPayAtHotel(bool $pay_at_hotel): void
    {
        $this->pay_at_hotel = $pay_at_hotel;
    }

    /**
     * @return bool
     */
    public function getPayAtHotel(): bool
    {
        return $this->pay_at_hotel;
    }

    /**
     * @param bool $non_refundable
     * @return void
     */
    public function setNonRefundable(bool $non_refundable): void
    {
        $this->non_refundable = $non_refundable;
    }

    /**
     * @return bool
     */
    public function getNonRefundable(): bool
    {
        return $this->non_refundable;
    }

    /**
     * @param string $meal_plan
     * @return void
     */
    public function setMealPlan(string $meal_plan): void
    {
        $this->meal_plan = $meal_plan;
    }

    /**
     * @return string
     */
    public function getMealPlan(): string
    {
        return $this->meal_plan;
    }

    /**
     * @param int $rate_id
     * @return void
     */
    public function setRateId(int $rate_id): void
    {
        $this->rate_id = $rate_id;
    }

    /**
     * @return int
     */
    public function getRateId(): int
    {
        return $this->rate_id;
    }

    /**
     * @param string $rate_description
     * @return void
     */
    public function setRateDescription(string $rate_description): void
    {
        $this->rate_description = $rate_description;
    }

    /**
     * @return string
     */
    public function getRateDescription(): string
    {
        return $this->rate_description;
    }

    /**
     * @param array $cancellation_policies
     * @return void
     */
    public function setCancellationPolicies(array $cancellation_policies): void
    {
        $this->cancellation_policies = $cancellation_policies;
    }

    /**
     * @return array
     */
    public function getCancellationPolicies(): array
    {
        return $this->cancellation_policies;
    }

    /**
     * @param bool $opaque
     * @return void
     */
    public function setOpaque(bool $opaque): void
    {
        $this->opaque = $opaque;
    }

    /**
     * @return bool
     */
    public function getOpaque(): bool
    {
        return $this->opaque;
    }

    /**
     * @param array $rooms
     * @return void
     */
    public function setRooms(array $rooms): void
    {
        $this->rooms = $rooms;
    }

    /**
     * @return array
     */
    public function getRooms(): array
    {
        return $this->rooms;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'total_price' => $this->getTotalPrice(),
            'total_tax' => $this->getTotalTax(),
            'total_fees' => $this->getTotalFees(),
            'total_net' => $this->getTotalNet(),
            'affiliate_service_charge' => $this->getAffiliateServiceCharge(),
            'currency' => $this->getCurrency(),
            'pay_now' => $this->getPayNow(),
            'pay_at_hotel' => $this->getPayAtHotel(),
            'non_refundable' => $this->getNonRefundable(),
            'meal_plan' => $this->getMealPlan(),
            'rate_id' => $this->getRateId(),
            'rate_description' => $this->getRateDescription(),
            'cancellation_policies' => $this->getCancellationPolicies(),
            'opaque' => $this->getOpaque(),
            'rooms' => $this->getRooms()
        ];
    }

}
