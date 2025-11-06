<?php

namespace Modules\API\PricingAPI\ResponseModels;

class RoomGroupsResponse extends BaseResponse
{
    private float $total_price;

    private float $total_tax;

    private float $total_fees;

    private float $total_net;

    private string $currency;

    private bool $pay_now;

    private bool $pay_at_hotel;

    private bool $non_refundable;

    private string $meal_plan;

    private int $rate_id;

    private string $rate_description;

    private array $cancellation_policies;

    private bool $opaque;

    private array $rooms;

    private array $deposits;

    public function setDeposits(array $deposits): void
    {
        $this->deposits = $deposits;
    }

    public function getDeposits(): array
    {
        return $this->deposits;
    }

    public function getTotalPrice(): float
    {
        return $this->total_price;
    }

    public function setTotalPrice(float $total_price): void
    {
        $this->total_price = $total_price;
    }

    public function setTotalTax(float $total_tax): void
    {
        $this->total_tax = $total_tax;
    }

    public function getTotalTax(): float
    {
        return $this->total_tax;
    }

    public function setTotalFees(float $total_fees): void
    {
        $this->total_fees = $total_fees;
    }

    public function getTotalFees(): float
    {
        return $this->total_fees;
    }

    public function setTotalNet(float $total_net): void
    {
        $this->total_net = $total_net;
    }

    public function getTotalNet(): float
    {
        return $this->total_net;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setPayNow(bool $pay_now): void
    {
        $this->pay_now = $pay_now;
    }

    public function getPayNow(): bool
    {
        return $this->pay_now;
    }

    public function setPayAtHotel(bool $pay_at_hotel): void
    {
        $this->pay_at_hotel = $pay_at_hotel;
    }

    public function getPayAtHotel(): bool
    {
        return $this->pay_at_hotel;
    }

    public function setNonRefundable(bool $non_refundable): void
    {
        $this->non_refundable = $non_refundable;
    }

    public function getNonRefundable(): bool
    {
        return $this->non_refundable;
    }

    public function setMealPlan(string $meal_plan): void
    {
        $this->meal_plan = $meal_plan;
    }

    public function getMealPlan(): string
    {
        return $this->meal_plan;
    }

    public function setRateId(int $rate_id): void
    {
        $this->rate_id = $rate_id;
    }

    public function getRateId(): int
    {
        return $this->rate_id;
    }

    public function setRateDescription(string $rate_description): void
    {
        $this->rate_description = $rate_description;
    }

    public function getRateDescription(): string
    {
        return $this->rate_description;
    }

    public function setCancellationPolicies(array $cancellation_policies): void
    {
        $this->cancellation_policies = $cancellation_policies;
    }

    public function getCancellationPolicies(): array
    {
        return $this->cancellation_policies;
    }

    public function setOpaque(bool $opaque): void
    {
        $this->opaque = $opaque;
    }

    public function getOpaque(): bool
    {
        return $this->opaque;
    }

    public function setRooms(array $rooms): void
    {
        $this->rooms = $rooms;
    }

    public function getRooms(): array
    {
        return $this->rooms;
    }

    public function toArray(): array
    {
        return [
            'total_price' => $this->getTotalPrice(),
            'total_tax' => $this->getTotalTax(),
            'total_fees' => $this->getTotalFees(),
            'total_net' => $this->getTotalNet(),
            'currency' => $this->getCurrency(),
            'pay_now' => $this->getPayNow(),
            'pay_at_hotel' => $this->getPayAtHotel(),
            'non_refundable' => $this->getNonRefundable(),
            'meal_plan' => $this->getMealPlan(),
            'rate_id' => $this->getRateId(),
            'rate_description' => $this->getRateDescription(),
            'cancellation_policies' => $this->getCancellationPolicies(),
            'deposits' => $this->getDeposits(),
            'opaque' => $this->getOpaque(),
            'rooms' => $this->getRooms(),
        ];
    }
}
