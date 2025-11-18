<?php

namespace Modules\API\PricingAPI\Resolvers\TaxAndFees;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Modules\API\PricingAPI\ResponseModels\TaxFee\RateItemTaxFee;
use Modules\API\PricingAPI\ResponseModels\TaxFee\TransformedRate;

class ExpediaTaxAndFeeResolver extends BaseTaxAndFeeResolver
{
    /**
     * @var RateItemTaxFee[] Taxes and fees applicable to the entire stay period (stay-level).
     */
    protected array $stayLevelItems = [];

    /**
     * Transform rates from the Expedia response.
     * Converts Expedia data into an array of nightly rates compatible with
     * the logic of applying taxes/fees, and extracts stay-level charges.
     *
     * @param  array  $rates  Rate data from the Expedia response (expected to be an array of room rate details).
     * @param  array  $repoTaxFees  Taxes and fees from the repository (not directly used here, but kept for signature).
     * @param  string  $checkin  The check-in date.
     * @return array Transformed nightly rates.
     */
    public function transformRates(array $rates, array $repoTaxFees, $checkin, $checkout): array
    {
        $transformedRates = [];
        $this->stayLevelItems = []; // Reset for new processing

        // Assume $rates is an array where the key is the roomKey, and the value is the room data.
        // Get the first (and potentially only) element to retrieve nightly data.
        $firstRoomRates = reset($rates);

        if (! is_array($firstRoomRates) || ! isset($firstRoomRates['nightly'])) {
            return [];
        }

        $nightlyRatesData = Arr::get($firstRoomRates, 'nightly', []);
        $feesData = Arr::get($firstRoomRates, 'fees', []);

        if (count($nightlyRatesData) === 0) {
            return [];
        }

        $currency = Arr::get($firstRoomRates, 'totals.exclusive.request_currency.currency', 'USD');
        $checkinDate = Carbon::parse($checkin);
        $checkoutDate = Carbon::parse($checkout);
        $numberOfNights = $checkinDate->diffInDays($checkoutDate);

        // --- 1. Process Nightly Rates, Taxes, and Fees (Daily/Nightly Items) ---
        foreach ($nightlyRatesData as $i => $expenseItems) {
            $baseRate = 0.0;
            $taxes = [];
            $fees = [];

            foreach ($expenseItems as $expenseItem) {
                if ($expenseItem['type'] === 'base_rate') {
                    $baseRate += $expenseItem['value'];
                } elseif (str_contains($expenseItem['type'], 'tax')) {
                    $taxes[] = [
                        'name' => $expenseItem['type'],
                        'value' => $expenseItem['value'],
                        'currency' => $expenseItem['currency'],
                        'type' => 'tax',
                    ];
                } elseif (str_contains($expenseItem['type'], 'fee')) {
                    $fees[] = [
                        'name' => $expenseItem['type'],
                        'value' => $expenseItem['value'],
                        'currency' => $expenseItem['currency'],
                        'type' => 'fee inclusive',
                    ];
                }
            }

            foreach ($feesData as $feeName => $expenseItem) {
                $feeValue = floatval($expenseItem['request_currency']['value']);
                $feePerNight = $feeValue / $numberOfNights;

                $fees[] = [
                    'name' => $feeName,
                    'type' => 'fee',
                    'description' => $feeName,
                    'value' => $feePerNight,
                ];
            }

            $transformedTaxes = $this->transformItemCollection($taxes, 'payAtBooking');
            $transformedFees = $this->transformItemCollection($fees, 'payAtBooking');

            $dailyTaxAndFeeTotal = array_reduce(
                array_merge($transformedTaxes, $transformedFees),
                fn ($carry, RateItemTaxFee $item) => $carry + $item->getAmount(),
                0.0
            );

            $transformedRate = new TransformedRate;
            $transformedRate->setCode('EXP_RATE_NIGHT_'.($i + 1));
            $transformedRate->setRateTimeUnit('Day');
            $transformedRate->setUnitMultiplier(1);
            $transformedRate->setEffectiveDate($checkinDate->copy()->addDays($i));
            $transformedRate->setExpireDate($checkinDate->copy()->addDays($i + 1));
            $transformedRate->setAmountBeforeTax($baseRate);
            $transformedRate->setAmountAfterTax($baseRate + $dailyTaxAndFeeTotal);
            $transformedRate->setCurrencyCode($currency);
            $transformedRate->setTotalAmountBeforeTax($baseRate);
            $transformedRate->setTotalAmountAfterTax($baseRate + $dailyTaxAndFeeTotal);
            $transformedRate->setTotalCurrencyCode($currency);
            $transformedRate->setTaxes($transformedTaxes);
            $transformedRate->setFees($transformedFees);

            $transformedRates[] = $transformedRate->toArray();
        }

        return $transformedRates;
    }

    /**
     * Converts a collection of items (daily, stay, or fees) into a standard rate format.
     *
     * @param  array  $items  Array of taxes/fees.
     * @param  string  $type  'payAtBooking' or 'payAtProperty'.
     * @return RateItemTaxFee[] Transformed items.
     */
    private function transformItemCollection(array $items, string $type): array
    {
        $transformedItems = [];
        $collectedBy = $type === 'payAtBooking' ? 'Vendor' : 'Direct';

        foreach ($items as $item) {
            $itemName = Arr::get($item, 'name', 'Unnamed Item');
            $typeFee = Arr::get($item, 'type') === 'fee' ? 'Inclusive' : 'Exclusive';

            $transformedItem = new RateItemTaxFee;
            $transformedItem->setType($typeFee);
            $transformedItem->setCode(str_replace(' ', '_', $itemName));
            $transformedItem->setLevel('hotel');
            $transformedItem->setCollectedBy($collectedBy);
            $transformedItem->setAmount((float) Arr::get($item, 'value', 0));
            $transformedItem->setDescription(Arr::get($item, 'description', $itemName));
            $transformedItem->setCurrency(Arr::get($item, 'currency', 'USD'));

            $transformedItems[] = $transformedItem;
        }

        return $transformedItems;
    }
}
