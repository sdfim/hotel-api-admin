<?php

namespace Modules\API\PricingRules\HBSI;

use App\Models\Supplier;
use Modules\API\PricingRules\BasePricingRulesApplier;
use Modules\API\PricingRules\PricingRulesApplierInterface;
use Modules\Enums\SupplierNameEnum;

class HbsiPricingRulesApplier extends BasePricingRulesApplier implements PricingRulesApplierInterface
{
    public function __construct(array $requestArray, array $pricingRules)
    {
        parent::__construct($requestArray, $pricingRules);

        $this->supplierId = Supplier::getSupplierId(SupplierNameEnum::HBSI->value);
    }

    /**
     * @var string[]
     */
    private array $fees = [
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
        'service charge'
    ];

    /**
     * @var string[]
     */
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
        'value added tax (vat)'
    ];

    /**
     * @param int $giataId
     * @param array{
     *     Rates: array,
     *     rateOccupancy: string
     *  } $roomsPricingArray
     * @param bool $b2b
     * @return array{
     *      total_price: float|int,
     *      total_tax: float|int,
     *      total_fees: float|int,
     *      total_net: float|int,
     *      affiliate_service_charge: float|int
     *  }
     */
    public function apply(int $giataId, array $roomsPricingArray, bool $b2b = true): array
    {
        $this->initPricingRulesProperties();

        // $roomsPricingArray['rateOccupancy'] is a string value in the following format:
        // 'number_of_adults-number_of_children-number_of_babies'. For example: '2-1-1'.
        // If there are no children or babies, then the format will appear as: '2-0-0'.
        $this->totalNumberOfGuests = array_sum(explode('-', $roomsPricingArray['rateOccupancy']));

        $roomTotals = $this->calculateRoomTotals($roomsPricingArray['Rates']);

        $this->updateTotals($roomTotals);

        foreach ($this->pricingRules as $pricingRule) {
            if ($this->validPricingRule($giataId, $pricingRule['conditions'])) {
                $this->applyPricingRulesLogic($pricingRule);
            }
        }

        return $this->totals($b2b);
    }

    /**
     * Calculates total_price(net_price, fees, taxes)
     *
     * @param array $roomPricing
     * @return array{total_price: float|int,total_tax: float|int,total_fees: float|int,total_net: float|int}
     */
    private function calculateRoomTotals(array $roomPricing): array
    {
        /**
         * @var array{total_price: float|int,total_tax: float|int,total_fees: float|int,total_net: float|int} $totals
         */
        $totals = [
            'total_price' => 0,
            'total_tax' => 0,
            'total_fees' => 0,
            'total_net' => 0
        ];

        if (array_key_first($roomPricing['Rate']) !== 0) {
            $roomPricingLoop[] = $roomPricing['Rate'];
        } else {
            $roomPricingLoop = $roomPricing['Rate'];
        }

        foreach ($roomPricingLoop as $rate) {
            $totals['total_net'] += (float)$rate['Total']['@attributes']['AmountBeforeTax'];

            // TODO: check this logic when the real data will be available
            if (isset($rate['Base']['Taxes']['Tax'])) {
                if (array_key_first($rate['Base']['Taxes']['Tax']) === 0) {
                    foreach ($rate['Base']['Taxes']['Tax'] as $tax) {
                        $totals = $this->calculateTaxAndFees($tax, $totals);
                    }
                } else {
                    $tax = $rate['Base']['Taxes']['Tax'];

                    $totals = $this->calculateTaxAndFees($tax, $totals);
                }
            }
        }

        $totals['total_price'] += $totals['total_net'] + $totals['total_fees'] + $totals['total_tax'];

        return $totals;
    }

    /**
     * @param $tax
     * @param $totals
     * @return int[]
     */
    private function calculateTaxAndFees($tax, $totals): array
    {
        $code = strtolower($tax['@attributes']['Code']);

        if (in_array(strtolower($tax['@attributes']['Code']), $this->fees)) {
            $totals['total_fees'] += (float)$tax['@attributes']['Amount'];
        }

        if (in_array($code, $this->taxes)) {
            $totals['total_tax'] += (float)$tax['@attributes']['Amount'];
        }

        return $totals;
    }
}
