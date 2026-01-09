<?php

namespace Modules\API\Suppliers\Oracle\Resolvers;

use Illuminate\Support\Carbon;
use Modules\API\PricingAPI\Resolvers\TaxAndFees\BaseTaxAndFeeResolver;
use Modules\API\PricingAPI\ResponseModels\TaxFee\RateItemTaxFee;
use Modules\API\PricingAPI\ResponseModels\TaxFee\TransformedRate;
use Modules\API\Suppliers\Contracts\Hotel\Resolvers\HotelTaxAndFeeResolverInterface;

class OracleTaxAndFeeResolver extends BaseTaxAndFeeResolver implements HotelTaxAndFeeResolverInterface
{
    /**
     * Transform rates from the Supplier response.
     *
     * @param  array  $rates  Rate data from the Supplier response (expected to be an array of room rate details).
     * @param  array  $repoTaxFees  Taxes and fees from the repository.
     * @param  string  $checkin  The check-in date.
     * @param  string  $checkout  The check-out date.
     * @return array Transformed nightly rates.
     */
    public function transformRates(array $rates, array $repoTaxFees, string $checkin = '', string $checkout = ''): array
    {
        $transformedRates = [];

        // 1. Итерируем по ставкам (rate) в ответе Oracle
        $oracleRates = $rates['rates']['rate'] ?? [];

        // В примере Oracle rates['rate'] – это массив с нумерованными ставками
        foreach ($oracleRates as $rateItem) {
            $transformedRates[] = $this->transformRate($rateItem, $rates);
        }

        // 2. Добавление НДС (VAT) - логика, скопированная из HBSI (оставлена без изменений)
        foreach ($transformedRates as &$rate) {
            if (! empty($repoTaxFees['vat'])) {
                $vat = array_values($repoTaxFees['vat'])[0];
                $vatPercentage = $vat['net_value'] ?? 0;

                // Calculate VAT
                // NOTE: Здесь предполагается, что $rate['amount_before_tax'] - это сумма ДО VAT,
                // а VAT должен быть вычислен и вычтен из нее.
                $vatAmount = ($rate['amount_before_tax'] / (1 + $vatPercentage / 100)) * ($vatPercentage / 100);

                // Add VAT to Taxes
                $tax = new RateItemTaxFee;
                $tax->setType('Inclusive');
                $tax->setAmount($vatAmount);
                $tax->setDescription('VAT');

                $rate['taxes'] = array_merge($rate['taxes'] ?? [], [$tax]);
                $rate['amount_before_tax'] = round(($rate['amount_before_tax'] ?? 0) - $vatAmount, 2);
            }
        }
        unset($rate); // Разрушить ссылку

        // 3. Разбиение на посуточные тарифы (Split Rates By Day)
        $transformedRates = $this->splitRatesByDay($transformedRates);

        return $transformedRates;
    }

    /**
     * Transform a single Oracle rate item into the intermediate structure.
     *
     * @param  array  $rateItem  The single rate element from Oracle 'rates'.
     * @param  array  $rootData  The root data array (to access shared keys like room_key).
     * @return array The transformed rate.
     */
    private function transformRate(array $rateItem, array $rootData): array
    {
        $taxes = [];
        $fees = [];

        $transformedRate = new TransformedRate;

        $baseAmount = (float) ($rateItem['base']['amountBeforeTax'] ?? 0);
        $totalAmount = (float) ($rateItem['total']['amountBeforeTax'] ?? 0);
        $currencyCode = $rateItem['base']['currencyCode'] ?? '';

        // **Расчет множителя (unit_multiplier)**
        $startDate = new \DateTime($rateItem['start']);
        $endDate = new \DateTime($rateItem['end']);
        // Количество дней = разница + 1 (так как период включает обе даты)
        $multiplier = $startDate->diff($endDate)->days + 1;

        // **Расчет включенного сбора/налога (Implicit Inclusive Tax/Fee)**
        // Разница между total и base, деленная на количество дней в этом блоке ставки.
        $implicitTaxFeeTotal = round($totalAmount - $baseAmount, 2);

        if ($implicitTaxFeeTotal > 0) {
            $implicitTaxFee = new RateItemTaxFee();
            $implicitTaxFee->setCode('IMPLICIT_TAX_FEE');
            $implicitTaxFee->setType('Inclusive');
            $implicitTaxFee->setCollectedBy('Vendor');
            $implicitTaxFee->setLevel('hotel');
            $implicitTaxFee->setAmount($implicitTaxFeeTotal);
            $implicitTaxFee->setDescription('Included Tax/Fee (Total - Base)');
            $implicitTaxFee->setCurrency($currencyCode);
            $taxes[] = $implicitTaxFee;
        }

        // **Базовые поля**
        $transformedRate->setCode($rootData['room_key'] ?? '');
        $transformedRate->setEffectiveDate(new Carbon($rateItem['start'] ?? ''));
        $transformedRate->setExpireDate(new Carbon($rateItem['end'] ?? ''));

        // **Суммы**
        // amount_before_tax - базовая посуточная сумма без включенных сборов (Base)
        $transformedRate->setAmountBeforeTax($baseAmount);
        // amount_after_tax - посуточная сумма с включенными сборами (Total)
        $transformedRate->setAmountAfterTax($totalAmount);
        $transformedRate->setCurrencyCode($currencyCode);

        // **Налоги и сборы**
        $transformedRate->setTaxes($taxes);
        $transformedRate->setFees($fees); // Здесь можно добавить логику для packages, если они являются сборами

        // **Дополнительные поля для splitRatesByDay**
        $transformedRateArr = $transformedRate->toArray();

        $transformedRateArr['rate_time_unit'] = 'Day';
        // Устанавливаем корректный множитель
        $transformedRateArr['unit_multiplier'] = $multiplier;

        // Общие суммы для всего блока ставки: посуточная сумма (Total) * множитель
        $transformedRateArr['total_amount_before_tax'] = $totalAmount * $multiplier;
        $transformedRateArr['total_amount_after_tax'] = $totalAmount * $multiplier;
        $transformedRateArr['total_currency_code'] = $currencyCode;

        return $transformedRateArr;
    }

    /**
     * Splits rates that span multiple days into individual nightly rates.
     *
     * @param  array  $transformedRates  The rates to split.
     * @return array The array of individual daily rates.
     */
    protected function splitRatesByDay(array $transformedRates): array
    {
        $dailyRates = [];
        $dayCounter = 0; // ИНИЦИАЛИЗАЦИЯ: Глобальный счетчик для последовательного именования

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

            // Используем общие суммы (total) для расчета посуточной суммы
            $dailyAmountBeforeTax = round($rate['total_amount_before_tax'] / $multiplier, 2);
            $dailyAmountAfterTax = round($rate['total_amount_after_tax'] / $multiplier, 2);

            for ($i = 0; $i < $multiplier; $i++) {
                $dayCounter++; // ИНКРЕМЕНТ: Увеличиваем глобальный счетчик для каждой ночи

                $dailyRate = $rate;

                $effectiveDate = (clone $startDate)->modify("+$i day")->format('Y-m-d');
                $expireDate = (clone $startDate)->modify('+'.($i + 1).' day')->format('Y-m-d');

                // ИСПОЛЬЗОВАНИЕ: Глобальный счетчик для последовательного кода
                $dailyRate['code'] = 'ORACLE_RATE_NIGHT_'.$dayCounter;
                $dailyRate['unit_multiplier'] = 1;
                $dailyRate['rate_time_unit'] = 'Day';
                $dailyRate['effective_date'] = $effectiveDate;
                $dailyRate['expire_date'] = $expireDate;

                $dailyRate['amount_before_tax'] = $dailyAmountBeforeTax;
                $dailyRate['amount_after_tax'] = $dailyAmountAfterTax;

                unset($dailyRate['total_amount_after_tax']);
                unset($dailyRate['total_amount_before_tax']);
                unset($dailyRate['total_currency_code']);
                unset($dailyRate['rate_time_unit']);
                unset($dailyRate['unit_multiplier']);

                $dailyRate['taxes'] = $rate['taxes'];
                $dailyRate['fees'] = $rate['fees'];

                $dailyRates[] = $dailyRate;
            }
        }

        return $dailyRates;
    }
}
