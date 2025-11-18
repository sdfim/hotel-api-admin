<?php

namespace Modules\API\PricingAPI\ResponseModels\TaxFee;

class RateItemTaxFee
{
    protected ?string $code = null;

    protected ?string $description = null;

    protected ?string $obe_action = null;

    protected bool $is_commissionable = false;

    protected ?string $type = null;

    protected ?string $level = null;

    protected ?string $collected_by;

    protected ?string $start_date = null;

    protected ?string $end_date = null;

    protected int|float|null $multiplier_fee = 1;

    protected ?float $displayable_rack_amount = null;

    protected ?float $displayable_amount = null;

    protected ?float $amount = null;

    protected ?float $rack_amount = null;

    protected ?string $value_type = null;

    protected ?string $currency = null;

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getObeAction(): ?string
    {
        return $this->obe_action;
    }

    public function setObeAction(?string $obe_action): void
    {
        $this->obe_action = $obe_action;
    }

    public function isCommissionable(): bool
    {
        return $this->is_commissionable;
    }

    public function setIsCommissionable(bool $is_commissionable): void
    {
        $this->is_commissionable = $is_commissionable;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(?string $level): void
    {
        $this->level = $level;
    }

    public function getCollectedBy(): ?string
    {
        return $this->collected_by;
    }

    public function setCollectedBy(?string $collected_by): void
    {
        $this->collected_by = $collected_by;
    }

    public function getStartDate(): ?string
    {
        return $this->start_date;
    }

    public function setStartDate(?string $start_date): void
    {
        $this->start_date = $start_date;
    }

    public function getEndDate(): ?string
    {
        return $this->end_date;
    }

    public function setEndDate(?string $end_date): void
    {
        $this->end_date = $end_date;
    }

    public function getMultiplierFee(): int|float|null
    {
        return $this->multiplier_fee;
    }

    public function setMultiplierFee(int|float|null $multiplier_fee): void
    {
        $this->multiplier_fee = $multiplier_fee;
    }

    public function getDisplayableRackAmount(): ?float
    {
        return $this->displayable_rack_amount;
    }

    public function setDisplayableRackAmount(?float $displayable_rack_amount): void
    {
        $this->displayable_rack_amount = $displayable_rack_amount;
    }

    public function getDisplayableAmount(): ?float
    {
        return $this->displayable_amount;
    }

    public function setDisplayableAmount(?float $displayable_amount): void
    {
        $this->displayable_amount = $displayable_amount;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): void
    {
        $this->amount = $amount;
    }

    public function getRackAmount(): ?float
    {
        return $this->rack_amount;
    }

    public function setRackAmount(?float $rack_amount): void
    {
        $this->rack_amount = $rack_amount;
    }

    public function getValueType(): ?string
    {
        return $this->value_type;
    }

    public function setValueType(?string $value_type): void
    {
        $this->value_type = $value_type;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): void
    {
        $this->currency = $currency;
    }

    public function toArray(): array
    {
        return [
            'code' => $this->getCode(),
            'description' => $this->getDescription(),
            'obe_action' => $this->getObeAction(),
            'is_commissionable' => $this->isCommissionable(),
            'type' => $this->getType(),
            'level' => $this->getLevel(),
            'collected_by' => $this->getCollectedBy(),
            'start_date' => $this->getStartDate(),
            'end_date' => $this->getEndDate(),
            'multiplier_fee' => $this->getMultiplierFee(),
            'displayable_rack_amount' => $this->getDisplayableRackAmount(),
            'displayable_amount' => $this->getDisplayableAmount(),
            'amount' => $this->getAmount(),
            'rack_amount' => $this->getRackAmount(),
            'value_type' => $this->getValueType(),
            'currency' => $this->getCurrency(),
        ];
    }
}
