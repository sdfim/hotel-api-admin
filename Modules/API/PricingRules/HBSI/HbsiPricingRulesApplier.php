<?php

namespace Modules\API\PricingRules\HBSI;

use Modules\API\PricingRules\PricingRulesApplierInterface;
use Modules\API\Tools\GeneralTools;

class HbsiPricingRulesApplier implements PricingRulesApplierInterface
{
    /**
     * @var array
     */
    private array $requestArray;
    /**
     * @var array
     */
    private array $pricingRule;

    public function __construct(array $requestArray, array $pricingRule)
    {
        $this->requestArray = $requestArray;
        $this->pricingRule = $pricingRule;
    }

    /**
     * @param int $giataId
     * @param array $roomsPricingArray
     * @param bool $b2b
     * @return array{
     *      total_price: float|int,
     *      total_tax: float|int,
     *      total_fees: float|int,
     *      total_net: float|int,
     *      affiliate_service_charge: float|int,
     *  }
     */
    public function apply(int $giataId, array $roomsPricingArray, bool $b2b = true): array
    {
        $pricingRule = $this->pricingRule[$giataId] ?? [];

        $firstRoomCapacityKey = array_key_first($roomsPricingArray);

        /**
         * @var array{total_price: float|int,total_tax: float|int,total_fees: float|int,total_net: float|int,affiliate_service_charge: float|int} $result
         */
        return  [
            'total_price' => 0,
            'total_tax' => 0,
            'total_fees' => 0,
            'total_net' => 0,
            'affiliate_service_charge' => 0,
        ];

    }
}
