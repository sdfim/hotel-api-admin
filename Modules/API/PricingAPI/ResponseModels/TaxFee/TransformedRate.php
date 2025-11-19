<?php

namespace Modules\API\PricingAPI\ResponseModels\TaxFee;

use Illuminate\Support\Carbon;

class TransformedRate
{
    protected ?string $code;

    protected Carbon $effective_date;

    protected Carbon $expire_date;

    protected float $amount_before_tax;

    protected float $amount_after_tax;

    protected string $currency_code;


    /** @var RateItemTaxFee[] */
    protected array $taxes = [];

    /** @var RateItemTaxFee[] */
    protected array $fees = [];

    /** @var RateItemTaxFee[] */
    protected array $stay = [];

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getEffectiveDate(): Carbon
    {
        return $this->effective_date;
    }

    public function setEffectiveDate(Carbon $effective_date): void
    {
        $this->effective_date = $effective_date;
    }

    public function getExpireDate(): Carbon
    {
        return $this->expire_date;
    }

    public function setExpireDate(Carbon $expire_date): void
    {
        $this->expire_date = $expire_date;
    }

    public function getAmountBeforeTax(): float
    {
        return $this->amount_before_tax;
    }

    public function setAmountBeforeTax(float $amount_before_tax): void
    {
        $this->amount_before_tax = $amount_before_tax;
    }

    public function getAmountAfterTax(): float
    {
        return $this->amount_after_tax;
    }

    public function setAmountAfterTax(float $amount_after_tax): void
    {
        $this->amount_after_tax = $amount_after_tax;
    }

    public function getCurrencyCode(): string
    {
        return $this->currency_code;
    }

    public function setCurrencyCode(string $currency_code): void
    {
        $this->currency_code = $currency_code;
    }

    public function getTaxes(): array
    {
        return $this->taxes;
    }

    public function setTaxes(array $taxes): void
    {
        $this->taxes = $taxes;
    }

    public function getFees(): array
    {
        return $this->fees;
    }

    public function setFees(array $fees): void
    {
        $this->fees = $fees;
    }

    public function getStay(): array
    {
        return $this->stay;
    }

    public function setStay($stay): void
    {
        $this->stay = $stay;
    }

    public function toArray(): array
    {
        return [
            'code' => $this->getCode(),
            'effective_date' => $this->getEffectiveDate()->toDateString(),
            'expire_date' => $this->getExpireDate()->toDateString(),
            'amount_before_tax' => $this->getAmountBeforeTax(),
            'amount_after_tax' => $this->getAmountAfterTax(),
            'currency_code' => $this->getCurrencyCode(),
            'taxes' => array_map(fn ($tax) => $tax->toArray(), $this->getTaxes()),
            'stay' => array_map(fn ($stay) => $stay->toArray(), $this->getStay()),
            'fees' => array_map(fn ($fee) => $fee->toArray(), $this->getFees()),
        ];
    }
}
