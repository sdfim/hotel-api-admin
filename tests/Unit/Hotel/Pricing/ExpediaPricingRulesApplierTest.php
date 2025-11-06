<?php

use Modules\API\PricingRules\Expedia\ExpediaPricingRulesApplier;

uses(Tests\TestCase::class);

beforeEach(function () {
    $requestArray = createMockRequestArrayForExpediaPricing();
    $pricingRules = createMockPricingRuleForExpediaPricing();
    $this->expediaPricingRulesApplier = new ExpediaPricingRulesApplier($requestArray, $pricingRules);
});

test('apply pricing rules to rooms', function () {
    $giataId = 1;
    $roomsPricingArray = getRoomsPricingArrayForExpediaPricing();

    $result = $this->expediaPricingRulesApplier->apply($giataId, $roomsPricingArray, '', '', '', '', '');

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['total_price', 'total_tax', 'total_fees', 'total_net'])
        ->and($result['total_price'])->not->toBeNull()
        ->and($result['total_tax'])->not->toBeNull()
        ->and($result['total_fees'])->not->toBeNull()
        ->and($result['total_net'])->not->toBeNull();

    $expectedResult = getExpectedResultForExpediaPricing();
    unset($result['validPricingRules']);

    expect($result)->toEqual($expectedResult);
});

function getExpectedResultForExpediaPricing(): array
{
    return [
        'total_price' => 2611.5,
        'total_tax' => 355.5,
        'total_fees' => 270.0,
        'total_net' => 2196.0,
        'commission_amount' => 0.0,
    ];
}

function createMockRequestArrayForExpediaPricing(): array
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
                'children_ages' => [2, 2],
            ],
            [
                'adults' => 3,
            ],
            [
                'adults' => 1,
                'children_ages' => [2, 0],
            ],
        ],
    ];
}

function createMockPricingRuleForExpediaPricing(): array
{
    return [
        [
            'name' => 'Test pricing rule',
            'rule_start_date' => '2024-02-05',
            'rule_expiration_date' => '2024-05-05',
            'manipulable_price_type' => 'total_price',
            'price_value' => 20,
            'price_value_type' => 'fixed_value',
            'price_value_target' => 'per_room',
            'conditions' => [
                ['field' => 'destination', 'compare' => '=', 'value_from' => 961, 'value_to' => null],
            ],
        ],
    ];
}

function getRoomsPricingArrayForExpediaPricing(): array
{
    return [
        '2-2,2' => [
            'nightly' => [
                [
                    [
                        'type' => 'base_rate',
                        'value' => '244.00',
                        'currency' => 'USD',
                    ],
                    [
                        'type' => 'tax_and_service_fee',
                        'value' => '39.50',
                        'currency' => 'USD',
                    ],
                ],
                [
                    [
                        'type' => 'base_rate',
                        'value' => '244.00',
                        'currency' => 'USD',
                    ],
                    [
                        'type' => 'tax_and_service_fee',
                        'value' => '39.50',
                        'currency' => 'USD',
                    ],
                ],
                [
                    [
                        'type' => 'base_rate',
                        'value' => '244.00',
                        'currency' => 'USD',
                    ],
                    [
                        'type' => 'tax_and_service_fee',
                        'value' => '39.50',
                        'currency' => 'USD',
                    ],
                ],
            ],
            'fees' => [
                'mandatory_fee' => [
                    'billable_currency' => [
                        'value' => '90.00',
                        'currency' => 'USD',
                    ],
                    'request_currency' => [
                        'value' => '90.00',
                        'currency' => 'USD',
                    ],
                ],
            ],
            'totals' => [
                'exclusive' => [
                    'billable_currency' => [
                        'value' => '732.00',
                        'currency' => 'USD',
                    ],
                    'request_currency' => [
                        'value' => '732.00',
                        'currency' => 'USD',
                    ],
                ],
                'inclusive' => [
                    'billable_currency' => [
                        'value' => '850.50',
                        'currency' => 'USD',
                    ],
                    'request_currency' => [
                        'value' => '850.50',
                        'currency' => 'USD',
                    ],
                ],
                'property_fees' => [
                    'billable_currency' => [
                        'value' => '90.00',
                        'currency' => 'USD',
                    ],
                    'request_currency' => [
                        'value' => '90.00',
                        'currency' => 'USD',
                    ],
                ],
            ],
        ],
        '1-2,0' => [
            'nightly' => [
                [
                    [
                        'type' => 'base_rate',
                        'value' => '244.00',
                        'currency' => 'USD',
                    ],
                    [
                        'type' => 'tax_and_service_fee',
                        'value' => '39.50',
                        'currency' => 'USD',
                    ],
                ],
                [
                    [
                        'type' => 'base_rate',
                        'value' => '244.00',
                        'currency' => 'USD',
                    ],
                    [
                        'type' => 'tax_and_service_fee',
                        'value' => '39.50',
                        'currency' => 'USD',
                    ],
                ],
                [
                    [
                        'type' => 'base_rate',
                        'value' => '244.00',
                        'currency' => 'USD',
                    ],
                    [
                        'type' => 'tax_and_service_fee',
                        'value' => '39.50',
                        'currency' => 'USD',
                    ],
                ],
            ],
            'fees' => [
                'mandatory_fee' => [
                    'billable_currency' => [
                        'value' => '90.00',
                        'currency' => 'USD',
                    ],
                    'request_currency' => [
                        'value' => '90.00',
                        'currency' => 'USD',
                    ],
                ],
            ],
            'totals' => [
                'exclusive' => [
                    'billable_currency' => [
                        'value' => '732.00',
                        'currency' => 'USD',
                    ],
                    'request_currency' => [
                        'value' => '732.00',
                        'currency' => 'USD',
                    ],
                ],
                'inclusive' => [
                    'billable_currency' => [
                        'value' => '850.50',
                        'currency' => 'USD',
                    ],
                    'request_currency' => [
                        'value' => '850.50',
                        'currency' => 'USD',
                    ],
                ],
                'property_fees' => [
                    'billable_currency' => [
                        'value' => '90.00',
                        'currency' => 'USD',
                    ],
                    'request_currency' => [
                        'value' => '90.00',
                        'currency' => 'USD',
                    ],
                ],
            ],
        ],
        '3' => [
            'nightly' => [
                [
                    [
                        'type' => 'base_rate',
                        'value' => '244.00',
                        'currency' => 'USD',
                    ],
                    [
                        'type' => 'tax_and_service_fee',
                        'value' => '39.50',
                        'currency' => 'USD',
                    ],
                ],
                [
                    [
                        'type' => 'base_rate',
                        'value' => '244.00',
                        'currency' => 'USD',
                    ],
                    [
                        'type' => 'tax_and_service_fee',
                        'value' => '39.50',
                        'currency' => 'USD',
                    ],
                ],
                [
                    [
                        'type' => 'base_rate',
                        'value' => '244.00',
                        'currency' => 'USD',
                    ],
                    [
                        'type' => 'tax_and_service_fee',
                        'value' => '39.50',
                        'currency' => 'USD',
                    ],
                ],
            ],
            'fees' => [
                'mandatory_fee' => [
                    'billable_currency' => [
                        'value' => '90.00',
                        'currency' => 'USD',
                    ],
                    'request_currency' => [
                        'value' => '90.00',
                        'currency' => 'USD',
                    ],
                ],
            ],
            'totals' => [
                'exclusive' => [
                    'billable_currency' => [
                        'value' => '732.00',
                        'currency' => 'USD',
                    ],
                    'request_currency' => [
                        'value' => '732.00',
                        'currency' => 'USD',
                    ],
                ],
                'inclusive' => [
                    'billable_currency' => [
                        'value' => '850.50',
                        'currency' => 'USD',
                    ],
                    'request_currency' => [
                        'value' => '850.50',
                        'currency' => 'USD',
                    ],
                ],
                'property_fees' => [
                    'billable_currency' => [
                        'value' => '90.00',
                        'currency' => 'USD',
                    ],
                    'request_currency' => [
                        'value' => '90.00',
                        'currency' => 'USD',
                    ],
                ],
            ],
        ],
    ];
}
