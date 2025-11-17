<?php

namespace Modules\API\PricingAPI\Resolvers\TaxAndFees;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Laravel\Octane\Exceptions\DdException;
use Modules\API\PricingAPI\ResponseModels\TaxFee\RateItemTaxFee;
use Modules\API\PricingAPI\ResponseModels\TaxFee\TransformedRate;

class HotelTraderTaxAndFeeResolver extends BaseTaxAndFeeResolver
{
    /**
     * Transform rates from the HotelTrader response.
     * Converts HotelTrader data (aggregated and daily) into an array of nightly rates,
     * compatible with the logic of applying taxes/fees.
     *
     * @param  array  $rates  Rate data from the HotelTrader response.
     * @param  array  $repoTaxFees  Taxes and fees from the repository.
     * @return array Transformed nightly rates.
     *
     * @throws DdException
     */
    public function transformRates(array $rates, array $repoTaxFees): array
    {
        $transformedRates = [];
        $dailyPrices = Arr::get($rates, 'dailyPrice', []);
        $dailyTaxes = Arr::get($rates, 'dailyTax', []);
        $taxInfo = Arr::get($rates, 'taxInfo', []);
        $currency = Arr::get($rates, 'currency', 'USD');
        $numberOfNights = count($dailyPrices);

        if ($numberOfNights === 0) {
            return [];
        }

        // Grouping taxInfo by date for easier lookup
        $dailyTaxesAndFees = [];
        foreach (Arr::get($taxInfo, 'payAtBooking', []) as $tax) {
            $dailyTaxesAndFees[$tax['date']]['payAtBooking'][] = $tax;
        }
        foreach (Arr::get($taxInfo, 'payAtProperty', []) as $fee) {
            $dailyTaxesAndFees[$fee['date']]['payAtProperty'][] = $fee;
        }

        // Determining the check-in date
        $rateDate = Carbon::now();
        if (! empty($dailyTaxesAndFees)) {
            $dates = array_keys($dailyTaxesAndFees);
            $rateDate = Carbon::parse($dates[0]);
        }

        for ($i = 0; $i < $numberOfNights; $i++) {
            $baseRate = (float) ($dailyPrices[$i] ?? 0);
            $dailyTax = (float) ($dailyTaxes[$i] ?? 0);

            $currentDate = $rateDate->copy()->addDays($i)->toDateString();
            $currentTaxInfo = Arr::get($dailyTaxesAndFees, $currentDate, []);

            // Transforming taxes and fees for the current night
            $taxes = $this->transformDailyTaxes(Arr::get($currentTaxInfo, 'payAtBooking', []), 'payAtBooking');
            $fees = $this->transformDailyTaxes(Arr::get($currentTaxInfo, 'payAtProperty', []), 'payAtProperty');

            $transformedRate = new TransformedRate;
            $transformedRate->setCode('HT_RATE_NIGHT_'.($i + 1));
            $transformedRate->setRateTimeUnit('Day');
            $transformedRate->setUnitMultiplier(1);
            $transformedRate->setEffectiveDate(Carbon::parse($currentDate));
            $transformedRate->setExpireDate(Carbon::parse($rateDate->copy()->addDays($i + 1)->toDateString()));
            $transformedRate->setAmountBeforeTax($baseRate);
            $transformedRate->setAmountAfterTax($baseRate + $dailyTax);
            $transformedRate->setCurrencyCode($currency);

            $transformedRate->setTotalAmountBeforeTax($baseRate);
            $transformedRate->setTotalAmountAfterTax($baseRate + $dailyTax);
            $transformedRate->setTotalCurrencyCode($currency);

            $transformedRate->setTaxes($taxes);
            $transformedRate->setFees($fees);

            $transformedRates[] = $transformedRate->toArray();
        }

        return $transformedRates;
    }

    /**
     * Converts daily taxes/fees from taxInfo into a standard rate format.
     *
     * @param  array  $feesTaxes  Array of taxes/fees for the day.
     * @param  string  $type  'payAtBooking' or 'payAtProperty'.
     * @return array Transformed items.
     */
    private function transformDailyTaxes(array $feesTaxes, string $type): array
    {
        $transformedItems = [];
        foreach ($feesTaxes as $item) {
            $itemNameLower = strtolower($item['name']);

            $transformedItem = new RateItemTaxFee;
            $transformedItem->setType('Exclusive');
            $transformedItem->setCode(str_replace(' ', '_', $item['name']));
            $transformedItem->setLevel('hotel');
            $transformedItem->setCollectedBy('Vendor');
            $transformedItem->setAmount((float) $item['value']);
            $transformedItem->setDescription($item['description'] ?? $item['name']);
            $transformedItem->setCurrency($item['currency'] ?? 'USD');

            $transformedItems[] = $transformedItem;
        }

        return $transformedItems;
    }
}
