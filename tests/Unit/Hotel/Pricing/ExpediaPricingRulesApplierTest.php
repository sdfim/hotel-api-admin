<?php

namespace Tests\Unit\Hotel\Pricing;

use PHPUnit\Framework\TestCase;
use Modules\API\PricingRules\Expedia\ExpediaPricingRulesApplier;

class ExpediaPricingRulesApplierTest extends TestCase
{
    private $expediaPricingRulesApplier;

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $requestArray = $this->createMockRequestArray();
        $pricingRule = $this->createMockPricingRule();
        $this->expediaPricingRulesApplier = new ExpediaPricingRulesApplier($requestArray, $pricingRule);
    }

    /**
     * @test
     * @return void
     */
    public function test_apply_pricing_rules_to_rooms()
    {
        $giataId = 1;
        $roomsPricingArray = $this->getRoomsPricingArray();

        $result = $this->expediaPricingRulesApplier->apply($giataId, $roomsPricingArray);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('total_price', $result);
        $this->assertArrayHasKey('total_tax', $result);
        $this->assertArrayHasKey('total_fees', $result);
        $this->assertArrayHasKey('total_net', $result);
        $this->assertArrayHasKey('affiliate_service_charge', $result);

        $this->assertNotEmpty($result['total_price']);
        $this->assertNotEmpty($result['total_tax']);
        $this->assertNotEmpty($result['total_fees']);
        $this->assertNotEmpty($result['total_net']);

        $expectedResult = $this->getExpectedResult();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return float[]
     */
    public function getExpectedResult(): array
    {
        return [
            "total_price" => 2551.5,
            "total_tax" => 355.5,
            "total_fees" => 270.0,
            "total_net" => 2196.0,
            "affiliate_service_charge" => 0.0
        ];
    }

    /**
     * @return array
     */
    public function createMockRequestArray(): array
    {
        return [
            'type' => 'hotel',
            'checkin' => '2024-02-01',
            'checkout' => '2024-02-04',
            'destination' => 961,
            'rating' => 4.0,
            'occupancy' => [
                [
                    'adults' => 2,
                    'children_ages' => [2,2]
                ],
                [
                    'adults' => 3
                ],
                [
                    'adults' => 1,
                    'children_ages' => [2,0]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function createMockPricingRule(): array
    {
        $priceValueTypeToApplyOptions = [
            'fixed_value',
            'percentage'
        ];

        $priceTypeToApplyOptions = [
            'total_price',
            'net_price',
            'rate_price'
        ];

        $priceValueFixedTypeToApplyOptions = [
            'per_guest',
            'per_room',
            'per_night'
        ];

        $channelId = 1;     // Channel::first()->id;
        $supplierId = 1;    // Supplier::first()->id;
        $giataId = 1;       // GiataProperty::where('city_id', 961)->first()->code;
        $today = now();

        $pricingRule = [
            'name' => 'Rule for $giataId',
            'property' => $giataId,
            'destination' => 'New York',
            'travel_date' => $today,
            'supplier_id' => $supplierId,
            'channel_id' => $channelId,
            'days' => 3,
            'nights' => 2,
            'rate_code' => rand(1000, 10000),
            'room_type' => 'test type',
            'meal_plan' => 'test meal plan',
            'rating' => $this->randomFloat(2.5, 4.0),
            'price_value_to_apply' => rand(1, 100),
            'rule_start_date' => $today,
            'rule_expiration_date' => $today->copy()->addDays(rand(30, 60)),
            'created_at' => $today,
            'updated_at' => $today,
        ];

        $pricingRule['number_rooms'] = 3;
        $pricingRule['room_guests'] = $pricingRule['number_rooms'] > 1 ? $pricingRule['number_rooms'] - 1 : 0;
        $pricingRule['total_guests'] = 10;
        $pricingRule['price_value_type_to_apply'] = $priceValueTypeToApplyOptions[rand(0, 1)];
        $pricingRule['price_type_to_apply'] = $priceTypeToApplyOptions[rand(0, 2)];
        if ($pricingRule['price_value_type_to_apply'] === 'fixed_value') {
            $pricingRule['price_value_fixed_type_to_apply'] = $priceValueFixedTypeToApplyOptions[rand(0, 2)];
        } else {
            $pricingRule['price_value_fixed_type_to_apply'] = null;
        }

        return $pricingRule;
    }

    /**
     * @return array[]
     */
    private function getRoomsPricingArray() : array
    {
        return [
            '2-2,2' => [
                'nightly' => [
                    [
                        [
                            'type' => 'base_rate',
                            'value' => '244.00',
                            'currency' => 'USD'
                        ],
                        [
                            'type' => 'tax_and_service_fee',
                            'value' => '39.50',
                            'currency' => 'USD'
                        ]
                    ],
                    [
                        [
                            'type' => 'base_rate',
                            'value' => '244.00',
                            'currency' => 'USD'
                        ],
                        [
                            'type' => 'tax_and_service_fee',
                            'value' => '39.50',
                            'currency' => 'USD'

                        ]
                    ],
                    [
                        [
                            'type' => 'base_rate',
                            'value' => '244.00',
                            'currency' => 'USD'
                        ],
                        [
                            'type' => 'tax_and_service_fee',
                            'value' => '39.50',
                            'currency' => 'USD'

                        ]
                    ]
                ],
                'fees' => [
                    'mandatory_fee' => [
                        'billable_currency' => [
                            'value' => '90.00',
                            'currency' => 'USD'
                        ],
                        'request_currency' => [
                            'value' => '90.00',
                            'currency' => 'USD'
                        ]
                    ]
                ],
                'totals' => [
                    'exclusive' => [
                        'billable_currency' => [
                            'value' => '732.00',
                            'currency' => 'USD'
                        ],
                        'request_currency' => [
                            'value' => '732.00',
                            'currency' => 'USD'
                        ]
                    ],
                    'inclusive' => [
                        'billable_currency' => [
                            'value' => '850.50',
                            'currency' => 'USD'
                        ],
                        'request_currency' => [
                            'value' => '850.50',
                            'currency' => 'USD'
                        ]
                    ],
                    'property_fees' => [
                        'billable_currency' => [
                            'value' => '90.00',
                            'currency' => 'USD'
                        ],
                        'request_currency' => [
                            'value' => '90.00',
                            'currency' => 'USD'
                        ]
                    ]
                ]
            ],
            '1-2,0' => [
                'nightly' => [
                    [
                        [
                            'type' => 'base_rate',
                            'value' => '244.00',
                            'currency' => 'USD'
                        ],
                        [
                            'type' => 'tax_and_service_fee',
                            'value' => '39.50',
                            'currency' => 'USD'
                        ]
                    ],
                    [
                        [
                            'type' => 'base_rate',
                            'value' => '244.00',
                            'currency' => 'USD'
                        ],
                        [
                            'type' => 'tax_and_service_fee',
                            'value' => '39.50',
                            'currency' => 'USD'
                        ]
                    ],
                    [
                        [
                            'type' => 'base_rate',
                            'value' => '244.00',
                            'currency' => 'USD'
                        ],
                        [
                            'type' => 'tax_and_service_fee',
                            'value' => '39.50',
                            'currency' => 'USD'
                        ]
                    ]
                ],
                'fees' => [
                    'mandatory_fee' => [
                        'billable_currency' => [
                            'value' => '90.00',
                            'currency' => 'USD'
                        ],
                        'request_currency' => [
                            'value' => '90.00',
                            'currency' => 'USD'
                        ]
                    ]
                ],
                'totals' => [
                    'exclusive' => [
                        'billable_currency' => [
                            'value' => '732.00',
                            'currency' => 'USD'
                        ],
                        'request_currency' => [
                            'value' => '732.00',
                            'currency' => 'USD'
                        ]
                    ],
                    'inclusive' => [
                        'billable_currency' => [
                            'value' => '850.50',
                            'currency' => 'USD'
                        ],
                        'request_currency' => [
                            'value' => '850.50',
                            'currency' => 'USD'
                        ]
                    ],
                    'property_fees' => [
                        'billable_currency' => [
                            'value' => '90.00',
                            'currency' => 'USD'
                        ],
                        'request_currency' => [
                            'value' => '90.00',
                            'currency' => 'USD'
                        ]
                    ]
                ]
            ],
            '3' => [
                'nightly' => [
                    [
                        [
                            'type' => 'base_rate',
                            'value' => '244.00',
                            'currency' => 'USD'
                        ],
                        [
                            'type' => 'tax_and_service_fee',
                            'value' => '39.50',
                            'currency' => 'USD'
                        ]
                    ],
                    [
                        [
                            'type' => 'base_rate',
                            'value' => '244.00',
                            'currency' => 'USD'
                        ],
                        [
                            'type' => 'tax_and_service_fee',
                            'value' => '39.50',
                            'currency' => 'USD'
                        ]
                    ],
                    [
                        [
                            'type' => 'base_rate',
                            'value' => '244.00',
                            'currency' => 'USD'
                        ],
                        [
                            'type' => 'tax_and_service_fee',
                            'value' => '39.50',
                            'currency' => 'USD'
                        ]
                    ]
                ],
                'fees' => [
                    'mandatory_fee' => [
                        'billable_currency' => [
                            'value' => '90.00',
                            'currency' => 'USD'
                        ],
                        'request_currency' => [
                            'value' => '90.00',
                            'currency' => 'USD'
                        ]
                    ]
                ],
                'totals' => [
                    'exclusive' => [
                        'billable_currency' => [
                            'value' => '732.00',
                            'currency' => 'USD'
                        ],
                        'request_currency' => [
                            'value' => '732.00',
                            'currency' => 'USD'
                        ]
                    ],
                    'inclusive' => [
                        'billable_currency' => [
                            'value' => '850.50',
                            'currency' => 'USD'
                        ],
                        'request_currency' => [
                            'value' => '850.50',
                            'currency' => 'USD'
                        ]
                    ],
                    'property_fees' => [
                        'billable_currency' => [
                            'value' => '90.00',
                            'currency' => 'USD'
                        ],
                        'request_currency' => [
                            'value' => '90.00',
                            'currency' => 'USD'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @param float $min
     * @param float $max
     * @param int $precision
     * @return float|int
     */
    private function randomFloat(float $min, float $max, int $precision = 2): float|int
    {
        $factor = pow(10, $precision);
        return mt_rand($min * $factor, $max * $factor) / $factor;
    }
}
