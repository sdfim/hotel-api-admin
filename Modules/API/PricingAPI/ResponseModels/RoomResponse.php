<?php

namespace Modules\API\PricingAPI\ResponseModels;

class RoomResponse extends BaseResponse
{
    private array $amenities = [];

    private array $capacity = [];

    private string $unified_room_code = '';

    private bool $distribution = false;

    private string $giata_room_code = '';

    private string $giata_room_name = '';

    private string $supplier_room_name = '';

    private int|string $supplier_room_id = '';

    private string $per_day_rate_breakdown = '';

    private array $bed_groups = [];

    private int|string $supplier_bed_groups = '';

    private array $links = [];

    private float $total_price = 0.0;

    private float $total_tax = 0.0;

    private float $total_fees = 0.0;

    private float $total_net = 0.0;

    private float $commissionable_amount = 0.0;

    private float $commission_amount = 0.0;

    private string $booking_item = '';

    private string $currency = '';

    private string $room_type = '';

    private string $rate_id = '';

    private string $rate_plan_code = '';

    private string $rate_name = '';

    private string $rate_description = '';

    private array $cancellation_policies = [];

    private bool $non_refundable = false;

    private string $meal_plan = '';

    private array $bed_configurations = [];

    private array $breakdown = [];

    private string $room_description = '';

    private bool $package_deal = false;

    private array $promotions = [];

    private ?string $penalty_date = '';

    private string $query_package = '';

    private ?array $deposits = [];

    private array $descriptive_content = [];

    private ?array $pricingRulesApplier = [];

    private array $informative_fees = [];

    public function setPricingRulesAppliers(array $pricingRulesApplier): void
    {
        $this->pricingRulesApplier = $pricingRulesApplier;
    }

    public function getPricingRulesAppliers(): array
    {
        return $this->pricingRulesApplier;
    }

    public function setDeposits(array $deposits): void
    {
        $this->deposits = $deposits;
    }

    public function getDeposits(): array
    {
        return $this->deposits;
    }

    public function setDescriptiveContent(array $descriptiveContent): void
    {
        $this->descriptive_content = $descriptiveContent;
    }

    public function getDescriptiveContent(): array
    {
        return $this->descriptive_content ?? [];
    }

    public function setQueryPackage(string $query_package): void
    {
        $this->query_package = $query_package;
    }

    public function getQueryPackage(): string
    {
        return $this->query_package;
    }

    public function getUnifiedRoomCode(): string
    {
        return $this->unified_room_code;
    }

    public function setUnifiedRoomCode(string $unified_room_code): void
    {
        $this->unified_room_code = $unified_room_code;
    }

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

    public function setCommissionAmount(float $commission_amount): void
    {
        $this->commission_amount = $commission_amount;
    }

    public function getCommissionAmount(): float
    {
        return $this->commission_amount;
    }

    public function getCommissionableAmount(): float
    {
        return $this->commissionable_amount;
    }

    public function setCommissionableAmount(float $commissionable_amount): void
    {
        $this->commissionable_amount = $commissionable_amount;
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

    public function setLinks(array $links): void
    {
        $this->links = $links;
    }

    public function getLinks(): array
    {
        return $this->links;
    }

    public function setBedGroups(array $bed_groups): void
    {
        $this->bed_groups = $bed_groups;
    }

    public function getBedGroups(): array
    {
        return $this->bed_groups;
    }

    public function setSupplierBedGroups(int|string $supplier_bed_groups): void
    {
        $this->supplier_bed_groups = $supplier_bed_groups;
    }

    public function getSupplierBedGroups(): int|string
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

    public function getPromotions(): array
    {
        return $this->promotions;
    }

    public function setPromotions(array $promotions): void
    {
        $this->promotions = $promotions;
    }

    public function getCapacity(): array
    {
        return $this->capacity;
    }

    public function setCapacity(array $capacity): void
    {
        $this->capacity = $capacity;
    }

    public function getAmenities(): array
    {
        return $this->amenities;
    }

    public function setAmenities(array $amenities): void
    {
        $this->amenities = $amenities;
    }

    public function isDistribution(): bool
    {
        return $this->distribution;
    }

    public function setDistribution(bool $distribution): void
    {
        $this->distribution = $distribution;
    }

    public function setInformativeFees(array $informative_fees): void
    {
        $this->informative_fees = $informative_fees;
    }

    public function getInformativeFees(): array
    {
        return $this->informative_fees;
    }

    public function toArray(): array
    {
        return [
            'amenities' => $this->getAmenities(),
            'capacity' => $this->getCapacity(),
            'unified_room_code' => $this->getUnifiedRoomCode(),
            'giata_room_code' => $this->getGiataRoomCode(),
            'giata_room_name' => $this->getGiataRoomName(),
            'supplier_room_name' => $this->getSupplierRoomName(),
            'per_day_rate_breakdown' => $this->getPerDayRateBreakdown(),
            'supplier_room_id' => $this->getSupplierRoomCode(),
            'distribution' => $this->isDistribution(),
            'query_package' => $this->getQueryPackage(),
            'bed_groups' => $this->getBedGroups(),
            'supplier_bed_groups' => $this->getSupplierBedGroups(),
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

            'pricing_rules_applier' => $this->getPricingRulesAppliers(),

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
            'deposits' => $this->getDeposits(),
            'descriptive_content' => $this->getDescriptiveContent(),
            'commissionable_amount' => $this->getCommissionableAmount(),
            'commission_amount' => $this->getCommissionAmount(),
            'informative_fees' => $this->getInformativeFees(),
        ];
    }

    public static function fromArray(array $data): self
    {
        $instance = new self;

        if (isset($data['amenities'])) {
            $instance->setAmenities($data['amenities']);
        }
        if (isset($data['capacity'])) {
            $instance->setCapacity($data['capacity']);
        }
        if (isset($data['unified_room_code'])) {
            $instance->setUnifiedRoomCode($data['unified_room_code']);
        }
        if (isset($data['giata_room_code'])) {
            $instance->setGiataRoomCode($data['giata_room_code']);
        }
        if (isset($data['giata_room_name'])) {
            $instance->setGiataRoomName($data['giata_room_name']);
        }
        if (isset($data['supplier_room_name'])) {
            $instance->setSupplierRoomName($data['supplier_room_name']);
        }
        if (isset($data['per_day_rate_breakdown'])) {
            $instance->setPerDayRateBreakdown($data['per_day_rate_breakdown']);
        }
        if (isset($data['supplier_room_id'])) {
            $instance->setSupplierRoomCode($data['supplier_room_id']);
        }
        if (isset($data['distribution'])) {
            $instance->setDistribution($data['distribution']);
        }
        if (isset($data['query_package'])) {
            $instance->setQueryPackage($data['query_package']);
        }
        if (isset($data['room_type'])) {
            $instance->setRoomType($data['room_type']);
        }
        if (isset($data['room_description'])) {
            $instance->setRoomDescription($data['room_description']);
        }
        if (isset($data['rate_id'])) {
            $instance->setRateId($data['rate_id']);
        }
        if (isset($data['rate_plan_code'])) {
            $instance->setRatePlanCode($data['rate_plan_code']);
        }
        if (isset($data['rate_name'])) {
            $instance->setRateName($data['rate_name']);
        }
        if (isset($data['rate_description'])) {
            $instance->setRateDescription($data['rate_description']);
        }
        if (isset($data['total_price'])) {
            $instance->setTotalPrice($data['total_price']);
        }
        if (isset($data['total_tax'])) {
            $instance->setTotalTax($data['total_tax']);
        }
        if (isset($data['total_fees'])) {
            $instance->setTotalFees($data['total_fees']);
        }
        if (isset($data['total_net'])) {
            $instance->setTotalNet($data['total_net']);
        }
        if (isset($data['pricing_rules_applier'])) {
            $instance->setPricingRulesAppliers($data['pricing_rules_applier']);
        }
        if (isset($data['currency'])) {
            $instance->setCurrency($data['currency']);
        }
        if (isset($data['booking_item'])) {
            $instance->setBookingItem($data['booking_item']);
        }
        if (isset($data['cancellation_policies'])) {
            $instance->setCancellationPolicies($data['cancellation_policies']);
        }
        if (isset($data['non_refundable'])) {
            $instance->setNonRefundable($data['non_refundable']);
        }
        if (isset($data['meal_plan'])) {
            $instance->setMealPlans($data['meal_plan']);
        }
        if (isset($data['bed_configurations'])) {
            $instance->setBedConfigurations($data['bed_configurations']);
        }
        if (isset($data['breakdown'])) {
            $instance->setBreakdown($data['breakdown']);
        }
        if (isset($data['package_deal'])) {
            $instance->setPackageDeal($data['package_deal']);
        }
        if (isset($data['penalty_date'])) {
            $instance->setPenaltyDate($data['penalty_date']);
        }
        if (isset($data['promotions'])) {
            $instance->setPromotions($data['promotions']);
        }
        if (isset($data['deposits'])) {
            $instance->setDeposits($data['deposits']);
        }
        if (isset($data['descriptive_content'])) {
            $instance->setDescriptiveContent($data['descriptive_content']);
        }
        if (isset($data['commissionable_amount'])) {
            $instance->setCommissionableAmount($data['commissionable_amount']);
        }
        if (isset($data['commission_amount'])) {
            $instance->setCommissionAmount($data['commission_amount']);
        }

        return $instance;
    }
}
