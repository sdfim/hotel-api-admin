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

    private float $markup;

    private string $booking_item;

    private string $currency;

    private string $room_type;

    private string $rate_id;

    private string $rate_plan_code;

    private string $rate_name;

    private string $rate_description;

    private array $cancellation_policies;

    private bool $non_refundable;

    private string $meal_plan;

    private array $bed_configurations;

    private array $breakdown;

    private string $room_description;

    private bool $package_deal = false;

    private array $promotions = [];

    private ?string $penalty_date = null;

    private string $query_package;

    /**
     * @param string $query_package
     * @return void
     */
    public function setQueryPackage(string $query_package): void
    {
        $this->query_package = $query_package;
    }

    /**
     * @return string
     */
    public function getQueryPackage(): string
    {
        return $this->query_package;
    }

    /**
     * @param string $room_description
     */
    public function setRoomDescription(string $room_description): void
    {
        $this->room_description = $room_description;
    }

    public function getRoomDescription(): string
    {
        return $this->room_description;
    }

    public function setRateName(string $rate_name): void
    {
        $this->rate_name = $rate_name;
    }

    public function getRateName(): string
    {
        return $this->rate_name;
    }

    public function setBreakdown(array $breakdown): void
    {
        $this->breakdown = $breakdown;
    }

    public function getBreakdown(): array
    {
        return $this->breakdown ?? [];
    }

    public function setBedConfigurations(array $bed_configurations): void
    {
        $this->bed_configurations = $bed_configurations;
    }

    public function getBedConfigurations(): array
    {
        return $this->bed_configurations ?? [];
    }

    public function setMealPlans(string $meal_plan): void
    {
        $this->meal_plan = $meal_plan;
    }

    public function getMealPlans(): string
    {
        return $this->meal_plan ?? '';
    }

    public function setCancellationPolicies(array $cancellation_policies): void
    {
        $this->cancellation_policies = $cancellation_policies;
    }

    public function getCancellationPolicies(): array
    {
        return $this->cancellation_policies ?? [];
    }

    public function setRoomType(string $room_type): void
    {
        $this->room_type = $room_type;
    }

    public function getRoomType(): string
    {
        return $this->room_type;
    }

    public function setRateId(string $rate_id): void
    {
        $this->rate_id = $rate_id;
    }

    public function setRatePlanCode(string $rate_plan_code): void
    {
        $this->rate_plan_code = $rate_plan_code;
    }

    public function getRatePlanCode(): string
    {
        return $this->rate_plan_code ?? '';
    }

    public function getRateId(): string
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

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setBookingItem(string $booking_item): void
    {
        $this->booking_item = $booking_item;
    }

    public function getBookingItem(): string
    {
        return $this->booking_item;
    }

    public function setTotalPrice(float $total_price): void
    {
        $this->total_price = $total_price;
    }

    public function getTotalPrice(): float
    {
        return $this->total_price;
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

    public function setMarkup(float $markup): void
    {
        $this->markup = $markup;
    }

    public function getMarkup(): float
    {
        return $this->markup;
    }

    public function setTotalNet(float $total_net): void
    {
        $this->total_net = $total_net;
    }

    public function getTotalNet(): float
    {
        return $this->total_net;
    }

    public function setLinks(array $links): void
    {
        $this->links = $links;
    }

    public function getLinks(): array
    {
        return $this->links;
    }

    public function setSupplierBedGroups(int $supplier_bed_groups): void
    {
        $this->supplier_bed_groups = $supplier_bed_groups;
    }

    public function getSupplierBedGroups(): int
    {
        return $this->supplier_bed_groups;
    }

    public function setSupplierRoomCode(int|string $supplier_room_id): void
    {
        $this->supplier_room_id = $supplier_room_id;
    }

    public function getSupplierRoomCode(): int|string
    {
        return $this->supplier_room_id;
    }

    public function setGiataRoomCode(string $giata_room_code): void
    {
        $this->giata_room_code = $giata_room_code;
    }

    public function getGiataRoomCode(): string
    {
        return $this->giata_room_code;
    }

    public function setGiataRoomName(string $giata_room_name): void
    {
        $this->giata_room_name = $giata_room_name;
    }

    public function getGiataRoomName(): string
    {
        return $this->giata_room_name;
    }

    public function setSupplierRoomName(string $supplier_room_name): void
    {
        $this->supplier_room_name = $supplier_room_name;
    }

    public function getSupplierRoomName(): string
    {
        return $this->supplier_room_name;
    }

    public function setPerDayRateBreakdown(string $per_day_rate_breakdown): void
    {
        $this->per_day_rate_breakdown = $per_day_rate_breakdown;
    }

    public function getPerDayRateBreakdown(): string
    {
        return $this->per_day_rate_breakdown;
    }

    public function setNonRefundable(bool $non_refundable): void
    {
        $this->non_refundable = $non_refundable;
    }

    public function getNonRefundable(): bool
    {
        return $this->non_refundable ?? false;
    }

    public function isPackageDeal(): bool
    {
        return $this->package_deal;
    }

    public function setPackageDeal(bool $package_deal): void
    {
        $this->package_deal = $package_deal;
    }

    public function getPenaltyDate(): ?string
    {
        return $this->penalty_date;
    }

    public function setPenaltyDate(?string $penalty_date): void
    {
        $this->penalty_date = $penalty_date;
    }

    /**
     * @return array
     */
    public function getPromotions(): array
    {
        return $this->promotions;
    }

    /**
     * @param array $promotions
     */
    public function setPromotions(array $promotions): void
    {
        $this->promotions = $promotions;
    }

    public function toArray(): array
    {
        return [
            'giata_room_code' => $this->getGiataRoomCode(),
            'giata_room_name' => $this->getGiataRoomName(),
            'supplier_room_name' => $this->getSupplierRoomName(),
            'per_day_rate_breakdown' => $this->getPerDayRateBreakdown(),
            'supplier_room_id' => $this->getSupplierRoomCode(),
            'query_package' => $this->getQueryPackage(),
            // 'supplier_bed_groups' => $this->getSupplierBedGroups(),
            'room_type' => $this->getRoomType(),
            'room_description' => $this->getRoomDescription(),
            'rate_id' => $this->getRateId(),
            'rate_plan_code' => $this->getRatePlanCode() ?? '',
            'rate_name' => $this->getRateName(),
            'rate_description' => $this->getRateDescription(),
            'total_price' => $this->getTotalPrice(),
            'total_tax' => $this->getTotalTax(),
            'total_fees' => $this->getTotalFees(),
            'total_net' => $this->getTotalNet(),
            'markup' => $this->getMarkup(),
            'currency' => $this->getCurrency(),
            // 'links' => $this->getLinks(),
            'booking_item' => $this->getBookingItem(),
            'cancellation_policies' => $this->getCancellationPolicies(),
            'non_refundable' => $this->getNonRefundable(),
            'meal_plan' => $this->getMealPlans(),
            'bed_configurations' => $this->getBedConfigurations(),
            'breakdown' => $this->getBreakdown(),
            'package_deal' => $this->isPackageDeal(),
            'penalty_date' => $this->getPenaltyDate(),
            'promotions' => $this->getPromotions(),
        ];
    }
}
