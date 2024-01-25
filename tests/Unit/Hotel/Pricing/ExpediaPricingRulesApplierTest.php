<?php

namespace Tests\Unit\Hotel\Pricing;

use Illuminate\Foundation\Testing\WithFaker;
use Modules\API\PricingRules\Expedia\ExpediaPricingRulesApplier;
use Modules\API\Tools\PricingRulesTools;
use Tests\TestCase;

class ExpediaPricingRulesApplierTest extends TestCase
{
    use WithFaker;

    private ExpediaPricingRulesApplier $expediaPricingRulesApplier;

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
                    'children_ages' => [2, 2]
                ],
                [
                    'adults' => 3
                ],
                [
                    'adults' => 1,
                    'children_ages' => [2, 0]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function createMockPricingRule(): array
    {
        $pricingRulesTools = new PricingRulesTools();

        $pricingRule = $pricingRulesTools->generatePricingRuleData('Test rule');

        $pricingRuleConditionsData = $pricingRulesTools->generatePricingRuleConditionsData();

        $pricingRule['conditions'] = $pricingRuleConditionsData;

        return $pricingRule;
    }

    /**
     * @return array[]
     */
    private function getRoomsPricingArray(): array
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
}
