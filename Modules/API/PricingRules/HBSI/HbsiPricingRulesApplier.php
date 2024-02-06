<?php

namespace Modules\API\PricingRules\HBSI;

use Modules\API\PricingRules\BasePricingRulesApplier;
use Modules\API\PricingRules\PricingRulesApplierInterface;

class HbsiPricingRulesApplier extends BasePricingRulesApplier implements PricingRulesApplierInterface
{
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
        foreach ($this->pricingRules as $pricingRule) {
            $validPricingRule = $this->validPricingRule($pricingRule['conditions'], $giataId);

            $priceValueType = (string)($pricingRule['price_value_type'] ?? '');

            $priceValue = (float)($pricingRule['price_value'] ?? 0);

            $manipulablePriceType = (string)($pricingRule['manipulable_price_type'] ?? '');

            $priceValueTarget = (string)($pricingRule['price_value_target'] ?? '');

            foreach ($this->requestArray['occupancy'] as $room) {
                $totalNumberOfGuestsInRoom = (int)array_sum($room);

                $roomsPricingKey = isset($room['children_ages']) ? $room['adults'] . '-' . implode(',', $room['children_ages']) : $room['adults'];

                $roomTotals = $this->calculateRoomTotals($roomsPricingArray[$roomsPricingKey]);

                // these values are calculated in the same way for all cases below, therefore they are moved to the top from each closure
                $this->totalTax += $roomTotals['total_tax'];

                $this->totalFees += $roomTotals['total_fees'];

                if ($validPricingRule) {
                    // calculate pricing for each room from request
                    $affiliateServiceCharge = 0;

                    if ($manipulablePriceType === 'total_price') {
                        $priceValueFromTotal = ($roomTotals['total_price'] * $priceValue) / 100;

                        $affiliateServiceCharge = match ($priceValueTarget) {
                            'per_guest' => match ($priceValueType) {
                                'percentage' => $roomTotals['total_price'] + ($totalNumberOfGuestsInRoom * $priceValueFromTotal),
                                'fixed_value' => $roomTotals['total_price'] + ($totalNumberOfGuestsInRoom * $priceValue)
                            },
                            'per_room' => match ($priceValueType) {
                                'percentage' => $roomTotals['total_price'] + $priceValueFromTotal,
                                'fixed_value' => $roomTotals['total_price'] + $priceValue
                            },
                            'per_night' => match ($priceValueType) {
                                'percentage' => $roomTotals['total_price'] + ($this->numberOfNights * $priceValueFromTotal),
                                'fixed_value' => $roomTotals['total_price'] + ($this->numberOfNights * $priceValue)
                            }
                        };

                        $this->totalNet += $roomTotals['total_net'];
                    }

                    // in case when supplier is Expedia total_price and rate_price should be calculated the same way
                    if ($manipulablePriceType === 'net_price' || $manipulablePriceType === 'rate_price') {
                        $priceValueFromTotalNet = ($roomTotals['total_net'] * $priceValue) / 100;

                        $affiliateServiceCharge = match ($priceValueTarget) {
                            'per_guest' => match ($priceValueType) {
                                'percentage' => $totalNumberOfGuestsInRoom * $priceValueFromTotalNet,
                                'fixed_value' => $totalNumberOfGuestsInRoom * $priceValue
                            },
                            'per_room' => match ($priceValueType) {
                                'percentage' => $priceValueFromTotalNet,
                                'fixed_value' => $priceValue
                            },
                            'per_night' => match ($priceValueType) {
                                'percentage' => $this->numberOfNights * $priceValueFromTotalNet,
                                'fixed_value' => $this->numberOfNights * $priceValue,
                            },
                            'default' => 0
                        };

                        $this->totalNet += $roomTotals['total_net'];
                    }

                    // these values are calculated in the same way for all $manipulablePriceType
                    $this->affiliateServiceCharge += $affiliateServiceCharge;

                    $this->totalPrice += $roomTotals['total_price'];
                } else {
                    $this->totalPrice += $roomTotals['total_price'];

                    $this->totalNet += $roomTotals['total_net'];
                }
            }
        }

        $this->affiliateServiceCharge = $b2b ? round($this->affiliateServiceCharge, 2) : 0.00;

        /**
         * @var array{total_price: float|int,total_tax: float|int,total_fees: float|int,total_net: float|int,affiliate_service_charge: float|int}
         */
        return [
            'total_price' => $this->totalPrice,
            'total_tax' => $this->totalTax,
            'total_fees' => $this->totalFees,
            'total_net' => $this->totalNet,
            'affiliate_service_charge' => $this->affiliateServiceCharge
        ];
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

        foreach ($roomPricing['Rates']['Rate'] as $rate) {
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
