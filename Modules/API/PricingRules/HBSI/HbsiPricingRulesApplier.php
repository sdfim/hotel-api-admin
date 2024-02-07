<?php

namespace Modules\API\PricingRules\HBSI;

use Modules\API\PricingRules\BasePricingRulesApplier;
use Modules\API\PricingRules\PricingRulesApplierInterface;

class HbsiPricingRulesApplier extends BasePricingRulesApplier implements PricingRulesApplierInterface
{
    /**
     * @param int $giataId
     * @param array{
     *     Rates: array,
     *     roomIndex: int
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
        $room = $this->requestArray['occupancy'][$roomsPricingArray['roomIndex']];

        $this->totalNumberOfGuestsInRoom = $this->totalNumberOfGuestsInRoom($room);

        $this->roomTotals = $this->calculateRoomTotals($roomsPricingArray['Rates']);

        foreach ($this->pricingRules as $pricingRule) {
            $this->validPricingRule = $this->validPricingRule($pricingRule['conditions'], $giataId);

            $this->setPricingRuleValues($pricingRule);

            $this->applyPricingRulesLogic();
        }

        return $this->totals($b2b);
    }

    /**
     * @param array $conditions
     * @param int $giataId
     * @return bool
     */
    private function validPricingRule(array $conditions, int $giataId): bool
    {
        $validPricingRule = true;

        foreach ($conditions as $condition) {
            if ($condition['field'] === 'property') {
                if ((int)$condition['value_from'] === $giataId) {
                    $validPricingRule = true;
                    break;
                } else {
                    $validPricingRule = false;
                }
            }
        }

        return $validPricingRule;
    }

    /**
     * Calculates total_price(net_price, fees, taxes)
     *
     * @param array $roomPricing
     * @return array{total_price: float|int,total_tax: float|int,total_fees: float|int,total_net: float|int}
     */
    private function calculateRoomTotals(array $roomPricing): array
    {
        $testRoomPricing = [
            "Rates" => [
                "Rate" => [
                    [
                        "@attributes" => [
                            "RateTimeUnit" => "Day",
                            "UnitMultiplier" => "2",
                            "EffectiveDate" => "2024-03-15",
                            "ExpireDate" => "2024-03-17"
                        ],
                        "Base" => [
                            "@attributes" => [
                                "AmountBeforeTax" => "400.00",
                                "AmountAfterTax" => "400.00",
                                "CurrencyCode" => "USD"
                            ],
                            "Taxes" => [
                                "Tax" => [
                                    [
                                        "@attributes" => [
                                            "Type" => "Inclusive",
                                            "Code" => "Occupancy Tax",
                                            "Percent" => "10",
                                            "Amount" => "36.36"
                                        ],
                                        "TaxDescription" => [
                                            "Text" => "Occupancy Tax"
                                        ]
                                    ],
                                    "@attributes" => [
                                        "Type" => "Inclusive",
                                        "Code" => "Sales tax",
                                        "Percent" => "10",
                                        "Amount" => "36.36"
                                    ],
                                    "TaxDescription" => [
                                        "Text" => "Sales tax"
                                    ]
                                ]
                            ]
                        ],
                        "Total" => [
                            "@attributes" => [
                                "AmountBeforeTax" => "800.00",
                                "AmountAfterTax" => "800.00",
                                "CurrencyCode" => "USD"
                            ]
                        ]
                    ],
                    [
                        "@attributes" => [
                            "RateTimeUnit" => "Day",
                            "UnitMultiplier" => "1",
                            "EffectiveDate" => "2024-03-17",
                            "ExpireDate" => "2024-03-18"
                        ],
                        "Base" => [
                            "@attributes" => [
                                "AmountBeforeTax" => "0.00",
                                "AmountAfterTax" => "0.00",
                                "CurrencyCode" => "USD"
                            ]
                        ],
                        "Total" => [
                            "@attributes" => [
                                "AmountBeforeTax" => "0.00",
                                "AmountAfterTax" => "0.00",
                                "CurrencyCode" => "USD"
                            ]
                        ]
                    ],
                    [
                        "@attributes" => [
                            "RateTimeUnit" => "Day",
                            "UnitMultiplier" => "2",
                            "EffectiveDate" => "2024-03-18",
                            "ExpireDate" => "2024-03-20"
                        ],
                        "Base" => [
                            "@attributes" => [
                                "AmountBeforeTax" => "400.00",
                                "AmountAfterTax" => "400.00",
                                "CurrencyCode" => "USD"
                            ],
                            "Taxes" => [
                                "Tax" => [
                                    [
                                        "@attributes" => [
                                            "Type" => "Inclusive",
                                            "Code" => "Occupancy Tax",
                                            "Percent" => "10",
                                            "Amount" => "36.36"
                                        ],
                                        "TaxDescription" => [
                                            "Text" => "Occupancy Tax"
                                        ]
                                    ],
                                    "@attributes" => [
                                        "Type" => "Inclusive",
                                        "Code" => "Sales tax",
                                        "Percent" => "10",
                                        "Amount" => "36.36"
                                    ],
                                    "TaxDescription" => [
                                        "Text" => "Sales tax"
                                    ]
                                ]
                            ]
                        ],
                        "Total" => [
                            "@attributes" => [
                                "AmountBeforeTax" => "800.00",
                                "AmountAfterTax" => "800.00",
                                "CurrencyCode" => "USD"
                            ]
                        ]
                    ],
                    [
                        "@attributes" => [
                            "RateTimeUnit" => "Day",
                            "UnitMultiplier" => "1",
                            "EffectiveDate" => "2024-03-20",
                            "ExpireDate" => "2024-03-21"
                        ],
                        "Base" => [
                            "@attributes" => [
                                "AmountBeforeTax" => "0.00",
                                "AmountAfterTax" => "0.00",
                                "CurrencyCode" => "USD"
                            ]
                        ],
                        "Total" => [
                            "@attributes" => [
                                "AmountBeforeTax" => "0.00",
                                "AmountAfterTax" => "0.00",
                                "CurrencyCode" => "USD"
                            ]
                        ]
                    ],
                    [
                        "@attributes" => [
                            "RateTimeUnit" => "Day",
                            "UnitMultiplier" => "1",
                            "EffectiveDate" => "2024-03-21",
                            "ExpireDate" => "2024-03-22"
                        ],
                        "Base" => [
                            "@attributes" => [
                                "AmountBeforeTax" => "400.00",
                                "AmountAfterTax" => "400.00",
                                "CurrencyCode" => "USD"
                            ],
                            "Taxes" => [
                                "Tax" => [
                                    "@attributes" => [
                                        "Type" => "Inclusive",
                                        "Code" => "Occupancy Tax",
                                        "Percent" => "10",
                                        "Amount" => "36.36"
                                    ],
                                    "TaxDescription" => [
                                        "Text" => "Occupancy Tax"
                                    ]
                                ]
                            ]
                        ],
                        "Total" => [
                            "@attributes" => [
                                "AmountBeforeTax" => "400.00",
                                "AmountAfterTax" => "400.00",
                                "CurrencyCode" => "USD"
                            ]
                        ]
                    ]
                ]
            ]
        ];

        /**
         * @var array{total_price: float|int,total_tax: float|int,total_fees: float|int,total_net: float|int} $totals
         */
        $totals = [
            'total_price' => 0,
            'total_tax' => 0,
            'total_fees' => 0,
            'total_net' => 0
        ];

        $fees = [
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

        $taxes = [
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

        foreach ($roomPricing['Rate'] as $rate) {
            $totals['total_net'] += $rate['Total']['@attributes']['AmountBeforeTax'];

            if (isset($rate['Base']['Taxes']['Tax'])) {
                foreach ($rate['Base']['Taxes']['Tax'] as $tax) {
                    $code = strtolower($tax['@attributes']['Code']);

                    if (in_array(strtolower($tax['@attributes']['Code']), $fees)) {
                        $totals['total_fees'] += (float)$tax['@attributes']['Amount'];
                    }

                    if (in_array($code, $taxes)) {
                        $totals['total_tax'] += (float)$tax['@attributes']['Amount'];
                    }
                }
            }
        }

        $totals['total_price'] += $totals['total_net'] + $totals['total_fees'] + $totals['total_tax'];

        return $totals;
    }
}
