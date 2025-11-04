<?php

namespace Modules\API\PricingRules\HotelTrader;

use App\Models\Supplier;
use Illuminate\Support\Arr;
use Modules\API\PricingRules\BasePricingRulesApplier;
use Modules\API\PricingRules\PricingRulesApplierInterface;

class HotelTraderPricingRulesApplier extends BasePricingRulesApplier implements PricingRulesApplierInterface
{
    public function __construct(array $requestArray, array $pricingRules)
    {
        parent::__construct($requestArray, $pricingRules);
        $this->supplierId = Supplier::getSupplierId('HotelTrader');
    }

    /**
     * @return array{
     *      total_price: float|int,
     *      total_tax: float|int,
     *      total_fees: float|int,
     *      total_net: float|int,
     *      markup: float|int
     *  }
     */
    public function apply(
        int $giataId,
        array $roomsPricingArray,
        string $roomName,
        string|int $roomCode,
        string|int $roomType,
        string|int $rateCode,
        string|int $srRoomId,
        bool $b2b = true
    ): array {
        $this->initPricingRulesProperties();

        $this->totalNumberOfGuests = Arr::get($roomsPricingArray, 'rateOccupancy', 1);

        $roomTotals = $this->calculateRoomTotals(Arr::get($roomsPricingArray, 'Rates', []));

        $this->updateTotals($roomTotals);

        $validPricingRules = [];
        foreach ($this->pricingRules as $pricingRule) {
            $params = [
                $giataId,
                $pricingRule['conditions'],
                $roomName,
                $roomCode,
                $roomType,
                $rateCode,
                $srRoomId,
                ['supplier_id', 'property', 'room_name', 'room_code', 'room_type', 'total_price', 'rate_code', 'room_type_cr'],
                $roomTotals['total_price'],
            ];
            if ($this->validPricingRule(...$params)) {
                $validPricingRules[] = $pricingRule;
            }
        }

        $strategy = config('pricing-rules.application_strategy');
        if ($strategy === 'cascading') {
            $this->applyCascadingPricingRulesLogic($validPricingRules);
        } else {
            $this->applyParallelPricingRulesLogic($validPricingRules);
        }

        $result = $this->totals();
        $result['validPricingRules'] = $validPricingRules;

        return $result;
    }

    /**
     * Calculates total_price(net_price, fees, taxes) for HotelTrader
     *
     * @return array{total_price: float|int,total_tax: float|int,total_fees: float|int,total_net: float|int}
     */
    private function calculateRoomTotals(array $roomPricing): array
    {
        $totals = [
            'total_price' => 0,
            'total_tax' => 0,
            'total_fees' => 0,
            'total_net' => 0,
            'commission_amount' => 0,
        ];
        // netPrice = base rate for the stay
        $totals['total_net'] = (float) ($roomPricing['netPrice'] ?? 0);
        // tax = total tax for the stay
        $totals['total_tax'] = (float) ($roomPricing['tax'] ?? 0);
        // grossPrice = total price for the stay (net + tax)
        $totals['total_price'] = (float) ($roomPricing['grossPrice'] ?? 0);
        // payAtProperty = fees to be paid at property
        $totals['total_fees'] = (float) ($roomPricing['payAtProperty'] ?? 0);
        // commissionAmount if present
        $totals['commission_amount'] = (float) ($roomPricing['commissionAmount'] ?? 0);

        return $totals;
    }
}
