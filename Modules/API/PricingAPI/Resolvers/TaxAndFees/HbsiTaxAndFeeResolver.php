<?php

namespace Modules\API\PricingAPI\Resolvers\TaxAndFees;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Modules\API\PricingAPI\Resolvers\Helpers\ServiceCalculationHelper;
use Modules\API\PricingAPI\ResponseModels\TaxFee\RateItemTaxFee;
use Modules\API\PricingAPI\ResponseModels\TaxFee\TransformedRate;

class HbsiTaxAndFeeResolver extends BaseTaxAndFeeResolver
{
    /**
     * @var string[]
     */
    public array $fees = [
        'application fee',
        'banquet service fee',
        'city hotel fee',
        'crib fee',
        'early checkout fee',
        'express handling fee',
        'extra person charge',
        'local fee',
        'maintenance fee',
        'package fee',
        'resort fee',
        'rollaway fee',
        'room service fee',
        'service charge',
    ];

    private array $taxes = [
        'assessment/license tax',
        'bed tax',
        'city tax',
        'country tax',
        'county tax',
        'energy tax',
        'exempt',
        'federal tax',
        'food & beverage tax',
        'goods and services tax (gst)',
        'insurance premium tax',
        'lodging tax',
        'miscellaneous',
        'occupancy tax',
        'room tax',
        'sales tax',
        'standard',
        'state tax',
        'surcharge',
        'surplus lines tax',
        'total tax',
        'tourism tax',
        'vat/gst tax',
        'value added tax (vat)',
    ];

    /**
     * Transform rates from the response.
     *
     * @param  array  $rates  The rates to transform.
     * @return array The transformed rates.
     */
    public function transformRates(array $rates, array $repoTaxFees): array
    {
        $transformedRates = [];

        if (isset($rates['Rate']['@attributes'])) {
            $rate = $rates['Rate'];
            $transformedRates[] = $this->transformRate($rate);
        } else {
            foreach ($rates['Rate'] as $rate) {
                $transformedRates[] = $this->transformRate($rate);
            }
        }

        $numberOfNights = array_sum(array_column($transformedRates, 'unit_multiplier'));

        foreach ($transformedRates as $rate) {
            if (! empty($repoTaxFees['vat'])) {
                $vat = array_values($repoTaxFees['vat'])[0];
                $vatPercentage = $vat['net_value'] ?? 0;

                // Calculate VAT
                $vatAmount = ($rate->getTotalAmountBeforeTax() / (1 + $vatPercentage / 100)) * ($vatPercentage / 100);
                $rate->setTotalAmountBeforeTax($rate->getTotalAmountBeforeTax() - $vatAmount);

                $vatRate = round($vatAmount / $numberOfNights, 2);

                // Add VAT to Taxes
                $tax = new RateItemTaxFee;
                $tax->setType('Inclusive');
                $tax->setAmount($vatRate);
                $tax->setDescription('VAT');

                $rate->setTaxes([...$rate->getTaxes(), $tax]);
                $rate->setAmountBeforeTax(round($rate->getAmountBeforeTax() - $vatRate, 2));
            }
        }

        $transformedRates = $this->splitRatesByDay($transformedRates);

        return $transformedRates;
    }

    protected function splitRatesByDay(array $transformedRates): array
    {
        $dailyRates = [];

        foreach ($transformedRates as $rate) {
            $multiplier = (int) ($rate['unit_multiplier'] ?? 1);

            if ($multiplier <= 0) {
                $dailyRates[] = $rate;

                continue;
            }

            try {
                $startDate = new \DateTime($rate['effective_date']);
            } catch (\Exception $e) {
                $dailyRates[] = $rate;

                continue;
            }

            $dailyAmountBeforeTax = round($rate['total_amount_before_tax'] / $multiplier, 2);
            $dailyAmountAfterTax = round($rate['total_amount_after_tax'] / $multiplier, 2);

            for ($i = 0; $i < $multiplier; $i++) {
                $dailyRate = $rate;

                $effectiveDate = (clone $startDate)->modify("+$i day")->format('Y-m-d');
                $expireDate = (clone $startDate)->modify('+'.($i + 1).' day')->format('Y-m-d');

                $dailyRate['code'] = 'HBSI_RATE_NIGHT_'.($i + 1);
                $dailyRate['unit_multiplier'] = 1;
                $dailyRate['rate_time_unit'] = 'Day';
                $dailyRate['effective_date'] = $effectiveDate;
                $dailyRate['expire_date'] = $expireDate;

                $dailyRate['amount_before_tax'] = $dailyAmountBeforeTax;
                $dailyRate['amount_after_tax'] = $dailyAmountAfterTax;
                $dailyRate['total_amount_before_tax'] = $dailyAmountBeforeTax;
                $dailyRate['total_amount_after_tax'] = $dailyAmountAfterTax;

                $dailyRate['taxes'] = $rate['taxes'];
                $dailyRate['fees'] = $rate['fees'];

                $dailyRates[] = $dailyRate;
            }
        }

        return $dailyRates;
    }

    /**
     * Transform a single rate from the response.
     *
     * @param  array  $rate  The rate to transform.
     * @return array The transformed rate.
     */
    private function transformRate(array $rate): array
    {
        $taxes = [];
        $fees = [];
        foreach (Arr::get($rate, 'Base.Taxes.Tax', []) as $tax) {
            if (in_array(strtolower($tax['@attributes']['Code']), $this->fees) || $tax['@attributes']['Type'] === 'PropertyCollects') {
                $fees[] = $tax;
            } else {
                $taxes[] = $tax;
            }
        }
        $transformedRate = new TransformedRate;
        $transformedRate->setCode($rate['@attributes']['Code'] ?? '');
        $transformedRate->setRateTimeUnit($rate['@attributes']['RateTimeUnit'] ?? '');
        $transformedRate->setUnitMultiplier((int) ($rate['@attributes']['UnitMultiplier'] ?? 0));
        $transformedRate->setEffectiveDate(new Carbon($rate['@attributes']['EffectiveDate'] ?? ''));
        $transformedRate->setExpireDate(new Carbon($rate['@attributes']['ExpireDate'] ?? ''));
        $transformedRate->setAmountBeforeTax((float) ($rate['Base']['@attributes']['AmountBeforeTax'] ?? 0));
        $transformedRate->setAmountAfterTax((float) ($rate['Base']['@attributes']['AmountAfterTax'] ?? 0));
        $transformedRate->setCurrencyCode($rate['Base']['@attributes']['CurrencyCode'] ?? '');

        $transformedRate->setTotalAmountBeforeTax((float) ($rate['Total']['@attributes']['AmountBeforeTax'] ?? 0));
        $transformedRate->setTotalAmountAfterTax((float) ($rate['Total']['@attributes']['AmountAfterTax'] ?? 0));
        $transformedRate->setTotalCurrencyCode($rate['Total']['@attributes']['CurrencyCode'] ?? '');

        $transformedRate->setTaxes($this->transformTaxes($taxes, $transformedRate->getCurrencyCode()));
        $transformedRate->setFees($this->transformTaxes($fees, $transformedRate->getCurrencyCode()));

        return $transformedRate->toArray();
    }

    /**
     * Transform taxes from the response.
     *
     * @param  array  $taxes  The taxes to transform.
     * @return array The transformed taxes.
     */
    private function transformTaxes(array $taxes, string $currency): array
    {
        $transformedTaxes = [];

        if (isset($taxes['@attributes'])) {
            $taxes = [$taxes];
        }

        foreach ($taxes as $tax) {
            $taxFee = new RateItemTaxFee;
            $taxFee->setType($tax['@attributes']['Type'] ?? '');
            $taxFee->setCode($tax['@attributes']['Code'] ?? '');
            $taxFee->setLevel('hotel');
            $taxFee->setCollectedBy('Vendor');
            $taxFee->setAmount((float) ($tax['@attributes']['Amount'] ?? 0));
            $taxFee->setDescription($tax['TaxDescription']['Text'] ?? '');
            $taxFee->setCurrency($currency);

            $transformedTaxes[] = $taxFee;
        }

        return $transformedTaxes;
    }
}
