<?php

namespace Modules\API\PricingAPI\ResponseModels;

class RoomResponse extends BaseResponse
{

    private string $giata_room_code;

    private string $giata_room_name;

    private string $supplier_room_name;

    private int|string $supplier_room_id;

    private string $per_day_rate_breakdown;

    private int $supplier_bed_groups;

    private array $links;

    private float $total_price;

    private float $total_tax;

    private float $total_fees;

    private float $total_net;

    private float $affiliate_service_charge;

    private string $booking_item;

    private string $currency;

    private string $room_type;

    private string $rate_id;

    private string $rate_plan_code;

    private string $rate_description;

    private array $cancellation_policies;

    private bool $non_refundable;

    private string $meal_plan;

    /**
     * @param string $meal_plan
     * @return void
     */
    public function setMealPlans(string $meal_plan): void
    {
        $this->meal_plan = $meal_plan;
    }

    /**
     * @return string
     */
    public function getMealPlans(): string
    {
        return $this->meal_plan;
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
     * @param string $room_type
     * @return void
     */
    public function setRoomType(string $room_type): void
    {
        $this->room_type = $room_type;
    }

    /**
     * @return string
     */
    public function getRoomType(): string
    {
        return $this->room_type;
    }

    /**
     * @param string $rate_id
     * @return void
     */
    public function setRateId(string $rate_id): void
    {
        $this->rate_id = $rate_id;
    }

    /**
     * @param string $rate_plan_code
     * @return void
     */
    public function setRatePlanCode(string $rate_plan_code): void
    {
        $this->rate_plan_code = $rate_plan_code;
    }

    /**
     * @return string
     */
    public function getRatePlanCode(): string
    {
        return $this->rate_plan_code;
    }

    /**
     * @return string
     */
    public function getRateId(): string
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
     * @param string $booking_item
     * @return void
     */
    public function setBookingItem(string $booking_item): void
    {
        $this->booking_item = $booking_item;
    }

    /**
     * @return string
     */
    public function getBookingItem(): string
    {
        return $this->booking_item;
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
     * @return float
     */
    public function getTotalPrice(): float
    {
        return $this->total_price;
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
     * @param float $affiliate_service_charge
     * @return void
     */
    public function setAffiliateServiceCharge(float $affiliate_service_charge): void
    {
        $this->affiliate_service_charge = $affiliate_service_charge;
    }

    /**
     * @return float
     */
    public function getAffiliateServiceCharge(): float
    {
        return $this->affiliate_service_charge;
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
     * @param array $links
     * @return void
     */
    public function setLinks(array $links): void
    {
        $this->links = $links;
    }

    /**
     * @return array
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @param int $supplier_bed_groups
     * @return void
     */
    public function setSupplierBedGroups(int $supplier_bed_groups): void
    {
        $this->supplier_bed_groups = $supplier_bed_groups;
    }

    /**
     * @return int
     */
    public function getSupplierBedGroups(): int
    {
        return $this->supplier_bed_groups;
    }

    /**
     * @param int|string $supplier_room_id
     * @return void
     */
    public function setSupplierRoomCode(int|string $supplier_room_id): void
    {
        $this->supplier_room_id = $supplier_room_id;
    }

    /**
     * @return int|string
     */
    public function getSupplierRoomCode(): int|string
    {
        return $this->supplier_room_id;
    }

    /**
     * @param string $giata_room_code
     * @return void
     */
    public function setGiataRoomCode(string $giata_room_code): void
    {
        $this->giata_room_code = $giata_room_code;
    }

    /**
     * @return string
     */
    public function getGiataRoomCode(): string
    {
        return $this->giata_room_code;
    }

    /**
     * @param string $giata_room_name
     * @return void
     */
    public function setGiataRoomName(string $giata_room_name): void
    {
        $this->giata_room_name = $giata_room_name;
    }

    /**
     * @return string
     */
    public function getGiataRoomName(): string
    {
        return $this->giata_room_name;
    }

    /**
     * @param string $supplier_room_name
     * @return void
     */
    public function setSupplierRoomName(string $supplier_room_name): void
    {
        $this->supplier_room_name = $supplier_room_name;
    }

    /**
     * @return string
     */
    public function getSupplierRoomName(): string
    {
        return $this->supplier_room_name;
    }

    /**
     * @param string $per_day_rate_breakdown
     * @return void
     */
    public function setPerDayRateBreakdown(string $per_day_rate_breakdown): void
    {
        $this->per_day_rate_breakdown = $per_day_rate_breakdown;
    }

    /**
     * @return string
     */
    public function getPerDayRateBreakdown(): string
    {
        return $this->per_day_rate_breakdown;
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
     * @return array
     */
    public function toArray(): array
    {
        return [
            'giata_room_code' => $this->getGiataRoomCode(),
            'giata_room_name' => $this->getGiataRoomName(),
            'supplier_room_name' => $this->getSupplierRoomName(),
            'per_day_rate_breakdown' => $this->getPerDayRateBreakdown(),
            'supplier_room_id' => $this->getSupplierRoomCode(),
            // 'supplier_bed_groups' => $this->getSupplierBedGroups(),
            'room_type' => $this->getRoomType(),
            'rate_id' => $this->getRateId(),
            'rate_plan_code' => $this->getRatePlanCode() ?? '',
            'rate_description' => $this->getRateDescription(),
            'total_price' => $this->getTotalPrice(),
            'total_tax' => $this->getTotalTax(),
            'total_fees' => $this->getTotalFees(),
            'total_net' => $this->getTotalNet(),
            'affiliate_service_charge' => $this->getAffiliateServiceCharge(),
            'currency' => $this->getCurrency(),
            // 'links' => $this->getLinks(),
            'booking_item' => $this->getBookingItem(),
            'cancellation_policies' => $this->getCancellationPolicies(),
            'non_refundable' => $this->getNonRefundable(),
            'meal_plan' => $this->getMealPlans(),
        ];
    }
}
