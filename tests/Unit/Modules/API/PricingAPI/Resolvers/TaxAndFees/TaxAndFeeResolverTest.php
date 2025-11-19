<?php

namespace Tests\Unit\Modules\API\PricingAPI\Resolvers\TaxAndFees;

/**
 * Class TaxAndFeeResolverTest
 *
 * This class tests the TaxAndFeeResolver functionality.
 *
 * @todo Get data from the database instead of hardcoding it.
 */
class TaxAndFeeResolverTest extends BaseTaxAndFeeResolverTest
{
    /**
     * Tests for applying repo tax fees with various scenarios
     * Taxes with Percentage and Amount value types
     */
    public function test_tax_percentage_per_room(): void
    {
        // Arrange
        $baseNetRate = 125;
        $baseRackRate = 125;
        $nights = 7;
        $transformedRates = $this->createBaseTransformedRates($baseNetRate, $nights, $baseRackRate);

        $repoTaxFeesInput = [
            1002 => [
                'add' => [
                    5 => [
                        'id' => 5,
                        'product_id' => 2,
                        'room_id' => null,
                        'rate_id' => null,
                        'start_date' => '2025-06-10T00:00:00.000000Z',
                        'end_date' => null,
                        'supplier_id' => 2,
                        'action_type' => 'add',
                        'old_name' => null,
                        'name' => 'VAT',
                        'net_value' => 10.0,
                        'rack_value' => 10.0,
                        'type' => 'Tax',
                        'value_type' => 'Percentage',
                        'apply_type' => 'per_room',
                        'commissionable' => false,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'Vendor',
                        'rate_code' => null,
                        'unified_room_code' => null,
                        'currency' => 'USD',
                    ],
                ],
            ],
        ];

        $expectedResult = [
            'taxes' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'VAT',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 12.5 / 7,
                    'rack_amount' => 12.5 / 7,
                    'value_type' => 'Percentage',
                    'displayable_rack_amount' => 12.5 / 7,
                    'displayable_amount' => 12.5 / 7,
                    'currency' => 'USD',
                ],
            ],
            'fees' => [],
        ];

        // Act
        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput, 2, $nights);

        // Assert
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_tax_percentage_per_person(): void
    {
        // Arrange
        $baseNetRate = 125;
        $nights = 7;
        $transformedRates = $this->createBaseTransformedRates($baseNetRate, $nights);
        $repoTaxFeesInput = [
            1002 => [
                'add' => [
                    5 => [
                        'id' => 5,
                        'product_id' => 2,
                        'room_id' => null,
                        'rate_id' => null,
                        'start_date' => '2025-06-10T00:00:00.000000Z',
                        'end_date' => null,
                        'supplier_id' => 2,
                        'action_type' => 'add',
                        'old_name' => null,
                        'name' => 'VAT',
                        'net_value' => 10.0,
                        'rack_value' => 10.0,
                        'type' => 'Tax',
                        'value_type' => 'Percentage',
                        'apply_type' => 'per_person',
                        'commissionable' => false,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'Vendor',
                        'rate_code' => null,
                        'unified_room_code' => null,
                        'currency' => 'USD',
                    ],
                ],
            ],
        ];

        $expectedResult = [
            'taxes' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'VAT',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 25,
                    'rack_amount' => 25,
                    'value_type' => 'Percentage',
                    'displayable_rack_amount' => 25,
                    'displayable_amount' => 25,
                    'currency' => 'USD',
                ],
            ],
            'fees' => [],
        ];

        // Act
        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput, 2, $nights);

        // Assert
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_tax_percentage_per_night(): void
    {
        // Arrange
        $baseNetRate = 125;
        $transformedRates = $this->createBaseTransformedRates($baseNetRate, 7);

        $repoTaxFeesInput = [
            1002 => [
                'add' => [
                    5 => [
                        'id' => 5,
                        'product_id' => 2,
                        'room_id' => null,
                        'rate_id' => null,
                        'start_date' => '2025-06-10T00:00:00.000000Z',
                        'end_date' => null,
                        'supplier_id' => 2,
                        'action_type' => 'add',
                        'old_name' => null,
                        'name' => 'VAT',
                        'net_value' => 10.0,
                        'rack_value' => 10.0,
                        'type' => 'Tax',
                        'value_type' => 'Percentage',
                        'apply_type' => 'per_night',
                        'commissionable' => false,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'Vendor',
                        'rate_code' => null,
                        'unified_room_code' => null,
                        'currency' => 'USD',
                    ],
                ],
            ],
        ];

        $expectedResult = [
            'taxes' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'VAT',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 87.5 / 7,
                    'rack_amount' => 87.5 / 7,
                    'value_type' => 'Percentage',
                    'displayable_rack_amount' => 87.5 / 7,
                    'displayable_amount' => 87.5 / 7,
                    'currency' => 'USD',
                ],
            ],
            'fees' => [],
        ];

        // Act
        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);

        // Assert
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_tax_percentage_per_person_per_night(): void
    {
        // Arrange
        $baseNetRate = 125;
        $transformedRates = $this->createBaseTransformedRates($baseNetRate, 7);

        $repoTaxFeesInput = [
            1002 => [
                'add' => [
                    5 => [
                        'id' => 5,
                        'product_id' => 2,
                        'room_id' => null,
                        'rate_id' => null,
                        'start_date' => '2025-06-10T00:00:00.000000Z',
                        'end_date' => null,
                        'supplier_id' => 2,
                        'action_type' => 'add',
                        'old_name' => null,
                        'name' => 'VAT',
                        'net_value' => 10.0,
                        'rack_value' => 10.0,
                        'type' => 'Tax',
                        'value_type' => 'Percentage',
                        'apply_type' => 'per_night_per_person',
                        'commissionable' => false,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'Vendor',
                        'rate_code' => null,
                        'unified_room_code' => null,
                        'currency' => 'USD',
                    ],
                ],
            ],
        ];

        $expectedResult = [
            'taxes' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'VAT',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 175 / 7,
                    'rack_amount' => 175 / 7,
                    'value_type' => 'Percentage',
                    'displayable_rack_amount' => 175 / 7,
                    'displayable_amount' => 175 / 7,
                    'currency' => 'USD',
                ],
            ],
            'fees' => [],
        ];

        // Act
        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);

        // Assert
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_tax_amount_per_room(): void
    {
        // Arrange
        $baseNetRate = 125;
        $baseRackRate = 125;
        $nights = 7;
        $transformedRates = $this->createBaseTransformedRates($baseNetRate, $nights, $baseRackRate);

        $repoTaxFeesInput = [
            1002 => [
                'add' => [
                    5 => [
                        'id' => 5,
                        'product_id' => 2,
                        'room_id' => null,
                        'rate_id' => null,
                        'start_date' => '2025-06-10T00:00:00.000000Z',
                        'end_date' => null,
                        'supplier_id' => 2,
                        'action_type' => 'add',
                        'old_name' => null,
                        'name' => 'VAT',
                        'net_value' => 10.0,
                        'rack_value' => 10.0,
                        'type' => 'Tax',
                        'value_type' => 'Amount',
                        'apply_type' => 'per_room',
                        'commissionable' => false,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'Vendor',
                        'rate_code' => null,
                        'unified_room_code' => null,
                        'currency' => 'USD',
                    ],
                ],
            ],
        ];

        $expectedResult = [
            'taxes' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'VAT',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 10 / 7,
                    'rack_amount' => 10 / 7,
                    'value_type' => 'Amount',
                    'displayable_rack_amount' => 10 / 7,
                    'displayable_amount' => 10 / 7,
                    'currency' => 'USD',
                ],
            ],
            'fees' => [],
        ];

        // Act
        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput, 2, $nights);

        // Assert
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_tax_amount_per_person(): void
    {
        // Arrange
        $baseNetRate = 125;
        $nights = 7;
        $transformedRates = $this->createBaseTransformedRates($baseNetRate, $nights);

        $repoTaxFeesInput = [
            1002 => [
                'add' => [
                    5 => [
                        'id' => 5,
                        'product_id' => 2,
                        'room_id' => null,
                        'rate_id' => null,
                        'start_date' => '2025-06-10T00:00:00.000000Z',
                        'end_date' => null,
                        'supplier_id' => 2,
                        'action_type' => 'add',
                        'old_name' => null,
                        'name' => 'VAT',
                        'net_value' => 10.0,
                        'rack_value' => 10.0,
                        'type' => 'Tax',
                        'value_type' => 'Amount',
                        'apply_type' => 'per_person',
                        'commissionable' => false,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'Vendor',
                        'rate_code' => null,
                        'unified_room_code' => null,
                        'currency' => 'USD',
                    ],
                ],
            ],
        ];

        $expectedResult = [
            'taxes' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'VAT',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 20 / 7,
                    'rack_amount' => 20 / 7,
                    'value_type' => 'Amount',
                    'displayable_rack_amount' => 20 / 7,
                    'displayable_amount' => 20 / 7,
                    'currency' => 'USD',
                ],
            ],
            'fees' => [],
        ];

        // Act
        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput, 2, $nights);

        // Assert
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_tax_amount_per_night(): void
    {
        // Arrange
        $baseNetRate = 125;
        $transformedRates = $this->createBaseTransformedRates($baseNetRate, 7);

        $repoTaxFeesInput = [
            1002 => [
                'add' => [
                    5 => [
                        'id' => 5,
                        'product_id' => 2,
                        'room_id' => null,
                        'rate_id' => null,
                        'start_date' => '2025-06-10T00:00:00.000000Z',
                        'end_date' => null,
                        'supplier_id' => 2,
                        'action_type' => 'add',
                        'old_name' => null,
                        'name' => 'VAT',
                        'net_value' => 10.0,
                        'rack_value' => 10.0,
                        'type' => 'Tax',
                        'value_type' => 'Amount',
                        'apply_type' => 'per_night',
                        'commissionable' => false,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'Vendor',
                        'rate_code' => null,
                        'unified_room_code' => null,
                        'currency' => 'USD',
                    ],
                ],
            ],
        ];

        $expectedResult = [
            'taxes' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'VAT',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 70 / 7,
                    'rack_amount' => 70 / 7,
                    'value_type' => 'Amount',
                    'displayable_rack_amount' => 70 / 7,
                    'displayable_amount' => 70 / 7,
                    'currency' => 'USD',
                ],
            ],
            'fees' => [],
        ];

        // Act
        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);

        // Assert
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    //    public function test_tax_amount_per_person_per_night(): void
    //    {
    //        // Arrange
    //        $baseNetRate = 125;
    //        $transformedRates = $this->createBaseTransformedRates($baseNetRate, 7);
    //
    //        $repoTaxFeesInput = [
    //            1002 => [
    //                'add' => [
    //                    5 => [
    //                        'id' => 5,
    //                        'product_id' => 2,
    //                        'room_id' => null,
    //                        'rate_id' => null,
    //                        'start_date' => '2025-06-10T00:00:00.000000Z',
    //                        'end_date' => null,
    //                        'supplier_id' => 2,
    //                        'action_type' => 'add',
    //                        'old_name' => null,
    //                        'name' => 'VAT',
    //                        'net_value' => 10.0,
    //                        'rack_value' => 10.0,
    //                        'type' => 'Tax',
    //                        'value_type' => 'Amount',
    //                        'apply_type' => 'per_night_per_person',
    //                        'commissionable' => false,
    //                        'fee_category' => 'mandatory',
    //                        'collected_by' => 'Vendor',
    //                        'rate_code' => null,
    //                        'unified_room_code' => null,
    //                        'currency' => 'USD',
    //                    ],
    //                ],
    //            ],
    //        ];
    //
    //        $expectedResult = [
    //            'taxes' => [
    //                [
    //                    'code' => 'OBE_5',
    //                    'description' => 'VAT',
    //                    'obe_action' => 'add',
    //                    'is_commissionable' => false,
    //                    'type' => 'Exclusive',
    //                    'collected_by' => 'Vendor',
    //                    'amount' => 175 / 7,
    //                    'rack_amount' => 175 / 7,
    //                    'value_type' => 'Amount',
    //                    'displayable_rack_amount' => 175 / 7,
    //                    'displayable_amount' => 175 / 7,
    //                    'currency' => 'USD',
    //                ],
    //            ],
    //            'fees' => [],
    //        ];
    //
    //        // Act
    //        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);
    //
    //        // Assert
    //        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    //    }

    public function test_tax_amount_per_room_and_per_person(): void
    {
        // Arrange
        $baseNetRate = 125;
        $baseRackRate = 125;
        $transformedRates = $this->createBaseTransformedRates($baseNetRate, 7, $baseRackRate);

        $repoTaxFeesInput = [
            1002 => [
                'add' => [
                    5 => [
                        'id' => 5,
                        'product_id' => 2,
                        'room_id' => null,
                        'rate_id' => null,
                        'start_date' => '2025-06-10T00:00:00.000000Z',
                        'end_date' => null,
                        'supplier_id' => 2,
                        'action_type' => 'add',
                        'old_name' => null,
                        'name' => 'VAT',
                        'net_value' => 10.0,
                        'rack_value' => 10.0,
                        'type' => 'Tax',
                        'value_type' => 'Amount',
                        'apply_type' => 'per_room',
                        'commissionable' => false,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'Vendor',
                        'rate_code' => null,
                        'unified_room_code' => null,
                        'currency' => 'USD',
                    ],
                    6 => [
                        'id' => 6,
                        'product_id' => 2,
                        'room_id' => null,
                        'rate_id' => null,
                        'start_date' => '2025-06-10T00:00:00.000000Z',
                        'end_date' => null,
                        'supplier_id' => 2,
                        'action_type' => 'add',
                        'old_name' => null,
                        'name' => 'VAT',
                        'net_value' => 12.0,
                        'rack_value' => 12.0,
                        'type' => 'Tax',
                        'value_type' => 'Amount',
                        'apply_type' => 'per_person',
                        'commissionable' => false,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'Vendor',
                        'rate_code' => null,
                        'unified_room_code' => null,
                        'currency' => 'USD',
                    ],
                ],
            ],
        ];

        $expectedResult = [
            'taxes' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'VAT',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 10 / 7,
                    'rack_amount' => 10 / 7,
                    'value_type' => 'Amount',
                    'displayable_rack_amount' => 10 / 7,
                    'displayable_amount' => 10 / 7,
                    'currency' => 'USD',
                ],
                [
                    'code' => 'OBE_6',
                    'description' => 'VAT',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 24 / 7,
                    'rack_amount' => 24 / 7,
                    'value_type' => 'Amount',
                    'displayable_rack_amount' => 24 / 7,
                    'displayable_amount' => 24 / 7,
                    'currency' => 'USD',
                ],
            ],
            'fees' => [],
        ];

        // Act
        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);

        // Assert
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_tax_percentage_amount_per_room_and_per_person(): void
    {
        // Arrange
        $baseNetRate = 125;
        $baseRackRate = 125;
        $transformedRates = $this->createBaseTransformedRates($baseNetRate, 7, $baseRackRate);

        $repoTaxFeesInput = [
            1002 => [
                'add' => [
                    5 => [
                        'id' => 5,
                        'product_id' => 2,
                        'room_id' => null,
                        'rate_id' => null,
                        'start_date' => '2025-06-10T00:00:00.000000Z',
                        'end_date' => null,
                        'supplier_id' => 2,
                        'action_type' => 'add',
                        'old_name' => null,
                        'name' => 'VAT',
                        'net_value' => 10.0,
                        'rack_value' => 10.0,
                        'type' => 'Tax',
                        'value_type' => 'Percentage',
                        'apply_type' => 'per_room',
                        'commissionable' => false,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'Vendor',
                        'rate_code' => null,
                        'unified_room_code' => null,
                        'currency' => 'USD',
                    ],
                    6 => [
                        'id' => 6,
                        'product_id' => 2,
                        'room_id' => null,
                        'rate_id' => null,
                        'start_date' => '2025-06-10T00:00:00.000000Z',
                        'end_date' => null,
                        'supplier_id' => 2,
                        'action_type' => 'add',
                        'old_name' => null,
                        'name' => 'VAT',
                        'net_value' => 12.0,
                        'rack_value' => 12.0,
                        'type' => 'Tax',
                        'value_type' => 'Amount',
                        'apply_type' => 'per_person',
                        'commissionable' => false,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'Vendor',
                        'rate_code' => null,
                        'unified_room_code' => null,
                        'currency' => 'USD',
                    ],
                ],
            ],
        ];

        $expectedResult = [
            'taxes' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'VAT',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 12.5 / 7,
                    'rack_amount' => 12.5 / 7,
                    'value_type' => 'Percentage',
                    'displayable_rack_amount' => 12.5 / 7,
                    'displayable_amount' => 12.5 / 7,
                    'currency' => 'USD',
                ],
                [
                    'code' => 'OBE_6',
                    'description' => 'VAT',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 24 / 7,
                    'rack_amount' => 24 / 7,
                    'value_type' => 'Amount',
                    'displayable_rack_amount' => 24 / 7,
                    'displayable_amount' => 24 / 7,
                    'currency' => 'USD',
                ],
            ],
            'fees' => [],
        ];

        // Act
        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);

        // Assert
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_tax_percentage_amount_per_room_and_per_person_fee_per_person(): void
    {
        // Arrange
        $baseNetRate = 125;
        $baseRackRate = 125;
        $transformedRates = $this->createBaseTransformedRates($baseNetRate, 7, $baseRackRate);

        $repoTaxFeesInput = [
            1002 => [
                'add' => [
                    5 => [
                        'id' => 5,
                        'product_id' => 2,
                        'room_id' => null,
                        'rate_id' => null,
                        'start_date' => '2025-06-10T00:00:00.000000Z',
                        'end_date' => null,
                        'supplier_id' => 2,
                        'action_type' => 'add',
                        'old_name' => null,
                        'name' => 'VAT',
                        'net_value' => 10.0,
                        'rack_value' => 10.0,
                        'type' => 'Tax',
                        'value_type' => 'Percentage',
                        'apply_type' => 'per_room',
                        'commissionable' => false,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'Vendor',
                        'rate_code' => null,
                        'unified_room_code' => null,
                        'currency' => 'USD',
                    ],
                    6 => [
                        'id' => 6,
                        'product_id' => 2,
                        'room_id' => null,
                        'rate_id' => null,
                        'start_date' => '2025-06-10T00:00:00.000000Z',
                        'end_date' => null,
                        'supplier_id' => 2,
                        'action_type' => 'add',
                        'old_name' => null,
                        'name' => 'VAT',
                        'net_value' => 12.0,
                        'rack_value' => 12.0,
                        'type' => 'Tax',
                        'value_type' => 'Amount',
                        'apply_type' => 'per_person',
                        'commissionable' => false,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'Vendor',
                        'rate_code' => null,
                        'unified_room_code' => null,
                        'currency' => 'USD',
                    ],
                    7 => [
                        'id' => 7,
                        'product_id' => 2,
                        'room_id' => null,
                        'rate_id' => null,
                        'start_date' => '2025-06-10T00:00:00.000000Z',
                        'end_date' => null,
                        'supplier_id' => 2,
                        'action_type' => 'add',
                        'old_name' => null,
                        'name' => 'VAT',
                        'net_value' => 10.0,
                        'rack_value' => 10.0,
                        'type' => 'Fee',
                        'value_type' => 'Percentage',
                        'apply_type' => 'per_room',
                        'commissionable' => false,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'Vendor',
                        'rate_code' => null,
                        'unified_room_code' => null,
                        'currency' => 'USD',
                    ],

                ],
            ],
        ];

        $expectedResult = [
            'taxes' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'VAT',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 12.5 / 7,
                    'rack_amount' => 12.5 / 7,
                    'value_type' => 'Percentage',
                    'displayable_rack_amount' => 12.5 / 7,
                    'displayable_amount' => 12.5 / 7,
                    'currency' => 'USD',
                ],
                [
                    'code' => 'OBE_6',
                    'description' => 'VAT',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 24 / 7,
                    'rack_amount' => 24 / 7,
                    'value_type' => 'Amount',
                    'displayable_rack_amount' => 24 / 7,
                    'displayable_amount' => 24 / 7,
                    'currency' => 'USD',
                ],
            ],
            'fees' => [
                [
                    'code' => 'OBE_7',
                    'description' => 'VAT',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 87.5 / 7,
                    'rack_amount' => 87.5 / 7,
                    'value_type' => 'Percentage',
                    'displayable_rack_amount' => 87.5 / 7,
                    'displayable_amount' => 87.5 / 7,
                    'currency' => 'USD',
                ],
            ],
        ];

        // Act
        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);

        // Assert
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    /**
     * Pre-new tests for applying repo tax fees with various scenarios
     */
    public function test_apply_repo_tax_fees_with_single_percentage_tax_on_net_rate(): void
    {
        // Arrange
        $baseNetRate = 200.00;
        $nights = 1;
        $transformedRates = $this->createBaseTransformedRates($baseNetRate, $nights);

        $repoTaxFeesInput = [
            1002 => [
                'add' => [
                    5 => [
                        'id' => 5,
                        'product_id' => 2,
                        'room_id' => null,
                        'rate_id' => null,
                        'start_date' => '2025-06-10T00:00:00.000000Z',
                        'end_date' => null,
                        'supplier_id' => 2,
                        'action_type' => 'add',
                        'old_name' => null,
                        'name' => 'VAT',
                        'net_value' => 10.0,
                        'rack_value' => 10.0,
                        'type' => 'Tax',
                        'value_type' => 'Percentage',
                        'apply_type' => 'per_room',
                        'commissionable' => false,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'Vendor',
                        'rate_code' => null,
                        'unified_room_code' => null,
                        'currency' => 'USD',
                    ],
                ],
            ],
        ];

        $expectedResult = [
            'taxes' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'VAT',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 20,
                    'rack_amount' => 20,
                    'value_type' => 'Percentage',
                    'displayable_rack_amount' => 20,
                    'displayable_amount' => 20,
                    'currency' => 'USD',
                ],
            ],
            'fees' => [],
        ];

        // Act
        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput, 2, $nights);

        // Assert
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_apply_repo_tax_fees_with_multiple_percentage_taxes_on_net_rate(): void
    {
        // Arrange
        $baseNetRate = 250.00;
        $nights = 1;
        $transformedRates = $this->createBaseTransformedRates($baseNetRate, $nights);

        $repoTaxFeesInput = [
            1002 => [
                'add' => [
                    5 => [ // VAT
                        'id' => 5,
                        'product_id' => 2,
                        'room_id' => null,
                        'rate_id' => null,
                        'start_date' => '2025-06-10T00:00:00.000000Z',
                        'end_date' => null,
                        'supplier_id' => 2,
                        'action_type' => 'add',
                        'old_name' => null,
                        'name' => 'VAT',
                        'net_value' => 16.0,
                        'rack_value' => 16.0,
                        'type' => 'Tax',
                        'value_type' => 'Percentage',
                        'apply_type' => 'per_room',
                        'commissionable' => false,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'Vendor',
                        'rate_code' => null,
                        'unified_room_code' => null,
                        'currency' => 'USD',
                    ],
                    6 => [ // Service Fee
                        'id' => 6,
                        'product_id' => 2,
                        'room_id' => null,
                        'rate_id' => null,
                        'start_date' => '2025-06-10T00:00:00.000000Z',
                        'end_date' => null,
                        'supplier_id' => 2,
                        'action_type' => 'add',
                        'old_name' => null,
                        'name' => 'Service Fee',
                        'net_value' => 10.0,
                        'rack_value' => 10.0,
                        'type' => 'Fee',
                        'value_type' => 'Percentage',
                        'apply_type' => 'per_room',
                        'commissionable' => false,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'Vendor',
                        'rate_code' => null,
                        'unified_room_code' => null,
                        'currency' => 'USD',
                    ],
                ],
            ],
        ];

        $expectedResult = [
            'taxes' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'VAT',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'value_type' => 'Percentage',
                    'amount' => 40,
                    'rack_amount' => 40,
                    'displayable_rack_amount' => 40,
                    'displayable_amount' => 40,
                    'currency' => 'USD',
                ],
            ],
            'fees' => [
                [
                    'code' => 'OBE_6',
                    'description' => 'Service Fee',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'value_type' => 'Percentage',
                    'amount' => 25.0,
                    'rack_amount' => 25.0,
                    'displayable_rack_amount' => 25.0,
                    'displayable_amount' => 25.0,
                    'currency' => 'USD',
                ],
            ],
        ];

        // Act
        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput, 2, $nights);

        // Assert
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_apply_repo_tax_fees_with_zero_percent_tax_on_net_rate(): void
    {
        // Arrange
        $baseNetRate = 150.00;
        $transformedRates = $this->createBaseTransformedRates($baseNetRate);

        $repoTaxFeesInput = [
            1002 => [
                'add' => [
                    7 => [ // Promotional Tax
                        'id' => 7,
                        'product_id' => 2,
                        'room_id' => null,
                        'rate_id' => null,
                        'start_date' => '2025-06-10T00:00:00.000000Z',
                        'end_date' => null,
                        'supplier_id' => 2,
                        'action_type' => 'add',
                        'old_name' => null,
                        'name' => 'Promotional Tax',
                        'net_value' => 0.0,
                        'rack_value' => 0.0,
                        'type' => 'Tax',
                        'value_type' => 'Percentage',
                        'apply_type' => 'per_room',
                        'commissionable' => false,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'Vendor',
                        'rate_code' => null,
                        'unified_room_code' => null,
                        'currency' => 'USD',
                    ],
                ],
            ],
        ];

        $expectedResult = [
            'taxes' => [
                [
                    'code' => 'OBE_7',
                    'description' => 'Promotional Tax',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 0.0,
                    'rack_amount' => 0.0,
                    'value_type' => 'Percentage',
                    'displayable_rack_amount' => 0.0,
                    'displayable_amount' => 0.0,
                    'currency' => 'USD',
                ],
            ],
            'fees' => [],
        ];

        // Act
        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);

        // Assert
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_apply_repo_tax_fees_with100_percent_tax_on_net_rate(): void
    {
        // Arrange
        $baseNetRate = 400.00;
        $nights = 1;
        $transformedRates = $this->createBaseTransformedRates($baseNetRate, $nights);

        $repoTaxFeesInput = [
            1002 => [
                'add' => [
                    8 => [ // Special Surcharge
                        'id' => 8,
                        'product_id' => 2,
                        'room_id' => null,
                        'rate_id' => null,
                        'start_date' => '2025-06-10T00:00:00.000000Z',
                        'end_date' => null,
                        'supplier_id' => 2,
                        'action_type' => 'add',
                        'old_name' => null,
                        'name' => 'Special Surcharge',
                        'net_value' => 100.0,
                        'rack_value' => 100.0,
                        'type' => 'Tax',
                        'value_type' => 'Percentage',
                        'apply_type' => 'per_room',
                        'commissionable' => false,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'Vendor',
                        'rate_code' => null,
                        'unified_room_code' => null,
                        'currency' => 'USD',
                    ],
                ],
            ],
        ];

        $expectedResult = [
            'taxes' => [
                [
                    'code' => 'OBE_8',
                    'description' => 'Special Surcharge',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'value_type' => 'Percentage',
                    'amount' => 400.0,
                    'rack_amount' => 400.0,
                    'displayable_rack_amount' => 400.0,
                    'displayable_amount' => 400.0,
                    'currency' => 'USD',
                ],
            ],
            'fees' => [],
        ];

        // Act
        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput, 2, $nights);

        // Assert
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_apply_repo_tax_fees_with_city_tax_resort_fee_and_service_fee(): void
    {
        // Arrange
        $baseNetRate = 125.00;
        $nights = 1;
        $transformedRates = $this->createBaseTransformedRates($baseNetRate, $nights);

        $repoTaxFeesInput = [
            1002 => [
                'add' => [
                    5 => [
                        'id' => 5,
                        'product_id' => 2,
                        'room_id' => null,
                        'rate_id' => null,
                        'start_date' => '2025-06-10T00:00:00.000000Z',
                        'end_date' => null,
                        'supplier_id' => 2,
                        'action_type' => 'add',
                        'old_name' => null,
                        'name' => 'City Tax',
                        'net_value' => 5.0,
                        'rack_value' => 5.0,
                        'type' => 'Tax',
                        'value_type' => 'Percentage',
                        'apply_type' => 'per_room',
                        'commissionable' => false,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'Direct',
                        'rate_code' => null,
                        'unified_room_code' => null,
                        'currency' => 'USD',
                    ],
                    8 => [
                        'id' => 8,
                        'product_id' => 2,
                        'room_id' => null,
                        'rate_id' => null,
                        'start_date' => '2025-06-01T00:00:00.000000Z',
                        'end_date' => null,
                        'supplier_id' => 2,
                        'action_type' => 'add',
                        'old_name' => null,
                        'name' => 'Resort Fee',
                        'net_value' => 30.0,
                        'rack_value' => 40.0,
                        'type' => 'Fee',
                        'value_type' => 'Amount',
                        'apply_type' => 'per_room',
                        'commissionable' => true,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'Vendor',
                        'rate_code' => null,
                        'unified_room_code' => null,
                        'currency' => 'USD',
                    ],
                    9 => [
                        'id' => 9,
                        'product_id' => 2,
                        'room_id' => null,
                        'rate_id' => null,
                        'start_date' => null,
                        'end_date' => null,
                        'supplier_id' => 2,
                        'action_type' => 'add',
                        'old_name' => null,
                        'name' => 'Service Fee',
                        'net_value' => 10.0,
                        'rack_value' => 20.0,
                        'type' => 'Fee',
                        'value_type' => 'Amount',
                        'apply_type' => 'per_person',
                        'commissionable' => true,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'Direct',
                        'rate_code' => null,
                        'unified_room_code' => null,
                        'currency' => 'USD',
                    ],
                ],
            ],
        ];

        $expectedResult = [
            'taxes' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'City Tax',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'Exclusive',
                    'collected_by' => 'Direct',
                    'amount' => 6.25,
                    'rack_amount' => 6.25,
                    'value_type' => 'Percentage',
                    'displayable_rack_amount' => 6.25,
                    'displayable_amount' => 6.25,
                    'currency' => 'USD',
                ],
            ],
            'fees' => [
                [
                    'code' => 'OBE_8',
                    'description' => 'Resort Fee',
                    'obe_action' => 'add',
                    'is_commissionable' => true,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 30.0,
                    'rack_amount' => 40.0,
                    'value_type' => 'Amount',
                    'displayable_rack_amount' => 40.0,
                    'displayable_amount' => 30.0,
                    'currency' => 'USD',
                ],
                [
                    'code' => 'OBE_9',
                    'description' => 'Service Fee',
                    'obe_action' => 'add',
                    'is_commissionable' => true,
                    'type' => 'Exclusive',
                    'collected_by' => 'Direct',
                    'amount' => 20.0 / 2,
                    'rack_amount' => 40.0 / 2,
                    'value_type' => 'Amount',
                    'displayable_rack_amount' => 40.0 / 2,
                    'displayable_amount' => 20.0 / 2,
                    'currency' => 'USD',
                ],
            ],
        ];

        // Act
        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput, 2, $nights);

        // Assert
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_apply_repo_tax_fees_with_tourism_tax_and_cleaning_fee(): void
    {
        // Arrange
        $baseNetRate = 150.00;
        $transformedRates = $this->createBaseTransformedRates($baseNetRate);

        $repoTaxFeesInput = [
            1002 => [
                'add' => [
                    5 => [
                        'id' => 5,
                        'product_id' => 2,
                        'room_id' => null,
                        'rate_id' => null,
                        'start_date' => '2025-06-10T00:00:00.000000Z',
                        'end_date' => null,
                        'supplier_id' => 2,
                        'action_type' => 'add',
                        'old_name' => null,
                        'name' => 'Tourism Tax',
                        'net_value' => 5.0,
                        'rack_value' => 5.0,
                        'type' => 'Tax',
                        'value_type' => 'Amount',
                        'apply_type' => 'per_night',
                        'commissionable' => false,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'Direct',
                        'rate_code' => null,
                        'unified_room_code' => null,
                        'currency' => 'USD',
                    ],
                    8 => [
                        'id' => 8,
                        'product_id' => 2,
                        'room_id' => null,
                        'rate_id' => null,
                        'start_date' => '2025-06-01T00:00:00.000000Z',
                        'end_date' => null,
                        'supplier_id' => 2,
                        'action_type' => 'add',
                        'old_name' => null,
                        'name' => 'Cleaning Fee',
                        'net_value' => 15.0,
                        'rack_value' => 30.0,
                        'type' => 'Fee',
                        'value_type' => 'Amount',
                        'apply_type' => 'per_night',
                        'commissionable' => true,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'Vendor',
                        'rate_code' => null,
                        'unified_room_code' => null,
                        'currency' => 'USD',
                    ],
                ],
            ],
        ];

        $expectedResult = [
            'taxes' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'Tourism Tax',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'Exclusive',
                    'collected_by' => 'Direct',
                    'amount' => 5.0,
                    'rack_amount' => 5.0,
                    'value_type' => 'Amount',
                    'displayable_rack_amount' => 5.0,
                    'displayable_amount' => 5.0,
                    'currency' => 'USD',
                ],
            ],
            'fees' => [
                [
                    'code' => 'OBE_8',
                    'description' => 'Cleaning Fee',
                    'obe_action' => 'add',
                    'is_commissionable' => true,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 15.0,
                    'rack_amount' => 30.0,
                    'value_type' => 'Amount',
                    'displayable_rack_amount' => 30.0,
                    'displayable_amount' => 15.0,
                    'currency' => 'USD',
                ],
            ],
        ];

        // Act
        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);

        // Assert
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_apply_repo_tax_fees_with_occupancy_tax_and_environmental_fee(): void
    {
        // Arrange
        $baseNetRate = 175.00;
        $transformedRates = $this->createBaseTransformedRates($baseNetRate);

        $repoTaxFeesInput = [
            1002 => [
                'add' => [
                    5 => [
                        'id' => 5,
                        'product_id' => 2,
                        'room_id' => null,
                        'rate_id' => null,
                        'start_date' => '2025-06-10T00:00:00.000000Z',
                        'end_date' => null,
                        'supplier_id' => 2,
                        'action_type' => 'add',
                        'old_name' => null,
                        'name' => 'Occupancy Tax',
                        'net_value' => 2.0,
                        'rack_value' => 2.0,
                        'type' => 'Tax',
                        'value_type' => 'Percentage',
                        'apply_type' => 'per_night_per_person',
                        'commissionable' => false,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'Direct',
                        'rate_code' => null,
                        'unified_room_code' => null,
                        'currency' => 'USD',
                    ],
                    8 => [
                        'id' => 8,
                        'product_id' => 2,
                        'room_id' => null,
                        'rate_id' => null,
                        'start_date' => '2025-06-01T00:00:00.000000Z',
                        'end_date' => null,
                        'supplier_id' => 2,
                        'action_type' => 'add',
                        'old_name' => null,
                        'name' => 'Enviromental Fee',
                        'net_value' => 5.0,
                        'rack_value' => 10.0,
                        'type' => 'Fee',
                        'value_type' => 'Amount',
                        'apply_type' => 'per_night_per_person',
                        'commissionable' => true,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'Vendor',
                        'rate_code' => null,
                        'unified_room_code' => null,
                        'currency' => 'USD',
                    ],
                ],
            ],
        ];

        $expectedResult = [
            'taxes' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'Occupancy Tax',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'Exclusive',
                    'collected_by' => 'Direct',
                    'value_type' => 'Percentage',
                    'amount' => 7,
                    'rack_amount' => 7,
                    'displayable_rack_amount' => 7,
                    'displayable_amount' => 7,
                    'currency' => 'USD',
                ],
            ],
            'fees' => [
                [
                    'code' => 'OBE_8',
                    'description' => 'Enviromental Fee',
                    'obe_action' => 'add',
                    'is_commissionable' => true,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 10.0 / 2,
                    'rack_amount' => 20.0 / 2,
                    'value_type' => 'Amount',
                    'displayable_rack_amount' => 20.0 / 2,
                    'displayable_amount' => 10.0 / 2,
                    'currency' => 'USD',
                ],
            ],
        ];

        // Act
        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);

        // Assert
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_get_transformed_breakdown_with_seven_night_stay_and_taxes(): void
    {
        // Arrange
        $transformedRates = [
            [
                'code' => '',
                'rate_time_unit' => 'Day',
                'unit_multiplier' => '7',
                'effective_date' => '2025-07-16',
                'expire_date' => '2025-07-23',
                'amount_before_tax' => '125.00',
                'amount_after_tax' => '125.00',
                'currency_code' => 'USD',
                'taxes' => [
                    [
                        'code' => 'OBE_5',
                        'description' => 'Tax',
                        'obe_action' => 'add',
                        'is_commissionable' => false,
                        'type' => 'Inclusive',
                        'collected_by' => 'vendor',
                        'amount' => 12.5,
                        'rack_amount' => 12.5,
                        'displayable_rack_amount' => 12.50,
                        'displayable_amount' => 12.50,
                        'value_type' => 'Amount',
                    ],
                ],
                'total_amount_before_tax' => '875.00',
                'total_amount_after_tax' => '875.00',
                'total_currency_code' => 'USD',
                'fees' => [
                    [
                        'code' => 'OBE_8',
                        'description' => 'Fee',
                        'obe_action' => 'add',
                        'is_commissionable' => true,
                        'type' => 'Inclusive',
                        'collected_by' => 'vendor',
                        'amount' => 18.75,
                        'rack_amount' => 25.0,
                        'displayable_amount' => 18.75,
                        'displayable_rack_amount' => 25.00,
                        'value_type' => 'Percentage',
                    ],
                    [
                        'code' => 'OBE_5',
                        'amount' => 25.0,
                        'rack_amount' => 50.0,
                        'displayable_amount' => 25.0,
                        'displayable_rack_amount' => 50.0,
                        'description' => 'Service',
                        'is_commissionable' => true,
                        'type' => 'Inclusive',
                    ],
                ],
            ],
        ];

        $expectedBreakdown = [
            'nightly' => [
                [
                    [
                        'amount' => 112.50,
                        'rack_amount' => 112.50,
                        'type' => 'base_rate',
                        'collected_by' => 'vendor',
                        'title' => 'Base Rate',
                        'level' => 'rate',
                    ],
                    [
                        'amount' => 12.5,
                        'rack_amount' => 12.5,
                        'description' => 'Tax',
                        'type' => 'Inclusive',
                        'collected_by' => 'vendor',
                        'obe_action' => 'add',
                        'displayable_rack_amount' => 12.5,
                        'displayable_amount' => 12.5,
                        'value_type' => 'Amount',
                        'code' => 'OBE_5',
                        'is_commissionable' => false,
                    ],
                ],
                [
                    [
                        'amount' => 112.50,
                        'rack_amount' => 112.50,
                        'type' => 'base_rate',
                        'collected_by' => 'vendor',
                        'title' => 'Base Rate',
                        'level' => 'rate',
                    ],
                    [
                        'amount' => 12.50,
                        'rack_amount' => 12.50,
                        'description' => 'Tax',
                        'type' => 'Inclusive',
                        'collected_by' => 'vendor',
                        'obe_action' => 'add',
                        'displayable_rack_amount' => 12.5,
                        'displayable_amount' => 12.5,
                        'value_type' => 'Amount',
                        'code' => 'OBE_5',
                        'is_commissionable' => false,
                    ],
                ],
                [
                    [
                        'amount' => 112.50,
                        'rack_amount' => 112.50,
                        'type' => 'base_rate',
                        'collected_by' => 'vendor',
                        'title' => 'Base Rate',
                        'level' => 'rate',
                    ],
                    [
                        'amount' => 12.50,
                        'rack_amount' => 12.50,
                        'description' => 'Tax',
                        'type' => 'Inclusive',
                        'collected_by' => 'vendor',
                        'obe_action' => 'add',
                        'displayable_rack_amount' => 12.5,
                        'displayable_amount' => 12.5,
                        'value_type' => 'Amount',
                        'code' => 'OBE_5',
                        'is_commissionable' => false,
                    ],
                ],
                [
                    [
                        'amount' => 112.50,
                        'rack_amount' => 112.50,
                        'type' => 'base_rate',
                        'collected_by' => 'vendor',
                        'title' => 'Base Rate',
                        'level' => 'rate',
                    ],
                    [
                        'amount' => 12.50,
                        'rack_amount' => 12.50,
                        'description' => 'Tax',
                        'type' => 'Inclusive',
                        'collected_by' => 'vendor',
                        'obe_action' => 'add',
                        'displayable_rack_amount' => 12.5,
                        'displayable_amount' => 12.5,
                        'value_type' => 'Amount',
                        'code' => 'OBE_5',
                        'is_commissionable' => false,
                    ],
                ],
                [
                    [
                        'amount' => 112.50,
                        'rack_amount' => 112.50,
                        'type' => 'base_rate',
                        'collected_by' => 'vendor',
                        'title' => 'Base Rate',
                        'level' => 'rate',
                    ],
                    [
                        'amount' => 12.50,
                        'rack_amount' => 12.50,
                        'description' => 'Tax',
                        'type' => 'Inclusive',
                        'collected_by' => 'vendor',
                        'obe_action' => 'add',
                        'displayable_rack_amount' => 12.5,
                        'displayable_amount' => 12.5,
                        'value_type' => 'Amount',
                        'code' => 'OBE_5',
                        'is_commissionable' => false,
                    ],
                ],
                [
                    [
                        'amount' => 112.50,
                        'rack_amount' => 112.50,
                        'type' => 'base_rate',
                        'collected_by' => 'vendor',
                        'title' => 'Base Rate',
                        'level' => 'rate',
                    ],
                    [
                        'amount' => 12.50,
                        'rack_amount' => 12.50,
                        'description' => 'Tax',
                        'type' => 'Inclusive',
                        'collected_by' => 'vendor',
                        'obe_action' => 'add',
                        'displayable_rack_amount' => 12.5,
                        'displayable_amount' => 12.5,
                        'value_type' => 'Amount',
                        'code' => 'OBE_5',
                        'is_commissionable' => false,
                    ],
                ],
                [
                    [
                        'amount' => 112.50,
                        'rack_amount' => 112.50,
                        'type' => 'base_rate',
                        'collected_by' => 'vendor',
                        'title' => 'Base Rate',
                        'level' => 'rate',
                    ],
                    [
                        'amount' => 12.50,
                        'rack_amount' => 12.50,
                        'description' => 'Tax',
                        'type' => 'Inclusive',
                        'collected_by' => 'vendor',
                        'obe_action' => 'add',
                        'displayable_rack_amount' => 12.5,
                        'displayable_amount' => 12.5,
                        'value_type' => 'Amount',
                        'code' => 'OBE_5',
                        'is_commissionable' => false,
                    ],
                ],
            ],
            'stay' => [],
            'fees' => [
                [
                    'amount' => 18.75,
                    'rack_amount' => 25.0,
                    'description' => 'Fee',
                    'type' => 'Inclusive',
                    'collected_by' => 'vendor',
                    'obe_action' => 'add',
                    'displayable_amount' => 18.75,
                    'displayable_rack_amount' => 25.0,
                    'value_type' => 'Percentage',
                    'code' => 'OBE_8',
                    'is_commissionable' => true,
                ],
                [
                    'amount' => 25.0,
                    'rack_amount' => 50.0,
                    'description' => 'Service',
                    'type' => 'Inclusive',
                    'displayable_amount' => 25.0,
                    'displayable_rack_amount' => 50.0,
                    'code' => 'OBE_5',
                    'is_commissionable' => true,
                ],
            ],
        ];

        // Act
        $actualBreakdown = $this->taxAndFeeResolver->getBreakdown($transformedRates);

        // Assert
        $this->assertEquals($expectedBreakdown, $actualBreakdown);
    }

    public function test_get_transformed_breakdown_with_three_night_stay_and_resort_service_fees(): void
    {
        // Arrange
        $transformedRates = [
            [
                'code' => '',
                'rate_time_unit' => 'Day',
                'unit_multiplier' => '3',
                'effective_date' => '2025-07-16',
                'expire_date' => '2025-07-19',
                'amount_before_tax' => '125.00',
                'amount_after_tax' => '125.00',
                'currency_code' => 'USD',
                'taxes' => [
                    [
                        'code' => 'OBE_5',
                        'description' => 'City Tax ',
                        'obe_action' => 'add',
                        'is_commissionable' => false,
                        'type' => 'Inclusive',
                        'MultiplierFee' => '1',
                        'collected_by' => 'vendor',
                        'amount' => 6.25,
                        'rack_amount' => 6.25,
                        'displayable_rack_amount' => 6.25,
                        'displayable_amount' => 6.25,
                        'value_type' => 'Amount',
                    ],
                ],
                'total_amount_before_tax' => '375.00',
                'total_amount_after_tax' => '375.00',
                'total_currency_code' => 'USD',
                'fees' => [
                    [
                        'code' => 'OBE_8',
                        'description' => 'Resort Fee',
                        'obe_action' => 'add',
                        'is_commissionable' => true,
                        'type' => 'Inclusive',
                        'MultiplierFee' => 1,
                        'collected_by' => 'vendor',
                        'amount' => 30.0,
                        'rack_amount' => 40.0,
                        'displayable_amount' => 30.0,
                        'displayable_rack_amount' => 40.0,
                        'value_type' => 'Amount',
                    ],
                    [
                        'code' => 'OBE_9',
                        'description' => 'Service Fee',
                        'obe_action' => 'add',
                        'is_commissionable' => true,
                        'type' => 'PropertyCollects',
                        'MultiplierFee' => 2,
                        'collected_by' => 'direct',
                        'amount' => 20.0,
                        'rack_amount' => 40.0,
                        'displayable_amount' => 20.0,
                        'displayable_rack_amount' => 40.0,
                        'value_type' => 'Amount',
                    ],
                ],
            ],
        ];

        $expectedBreakdown = [
            'nightly' => [
                [
                    [
                        'amount' => 118.75,
                        'rack_amount' => 118.75,
                        'type' => 'base_rate',
                        'collected_by' => 'vendor',
                        'title' => 'Base Rate',
                        'level' => 'rate',
                    ],
                    [
                        'amount' => 6.25,
                        'rack_amount' => 6.25,
                        'type' => 'Inclusive',
                        'collected_by' => 'vendor',
                        'code' => 'OBE_5',
                        'description' => 'City Tax ',
                        'obe_action' => 'add',
                        'is_commissionable' => false,
                        'MultiplierFee' => '1',
                        'displayable_rack_amount' => 6.25,
                        'value_type' => 'Amount',
                        'displayable_amount' => 6.25,
                    ],
                ],
                [
                    [
                        'amount' => 118.75,
                        'rack_amount' => 118.75,
                        'title' => 'Base Rate',
                        'type' => 'base_rate',
                        'level' => 'rate',
                        'collected_by' => 'vendor',
                    ],
                    [
                        'amount' => 6.25,
                        'rack_amount' => 6.25,
                        'type' => 'Inclusive',
                        'collected_by' => 'vendor',
                        'code' => 'OBE_5',
                        'description' => 'City Tax ',
                        'obe_action' => 'add',
                        'is_commissionable' => false,
                        'MultiplierFee' => '1',
                        'displayable_rack_amount' => 6.25,
                        'displayable_amount' => 6.25,
                        'value_type' => 'Amount',
                    ],
                ],
                [
                    [
                        'amount' => 118.75,
                        'rack_amount' => 118.75,
                        'title' => 'Base Rate',
                        'type' => 'base_rate',
                        'level' => 'rate',
                        'collected_by' => 'vendor',
                    ],
                    [
                        'amount' => 6.25,
                        'rack_amount' => 6.25,
                        'type' => 'Inclusive',
                        'collected_by' => 'vendor',
                        'code' => 'OBE_5',
                        'description' => 'City Tax ',
                        'obe_action' => 'add',
                        'is_commissionable' => false,
                        'MultiplierFee' => '1',
                        'displayable_rack_amount' => 6.25,
                        'displayable_amount' => 6.25,
                        'value_type' => 'Amount',
                    ],
                ],
            ],
            'stay' => [],
            'fees' => [
                [
                    'amount' => 30.0,
                    'rack_amount' => 40.0,
                    'type' => 'Inclusive',
                    'collected_by' => 'vendor',
                    'code' => 'OBE_8',
                    'description' => 'Resort Fee',
                    'obe_action' => 'add',
                    'is_commissionable' => true,
                    'MultiplierFee' => 1,
                    'displayable_rack_amount' => 40.0,
                    'value_type' => 'Amount',
                    'displayable_amount' => 30.0,
                ],
                [
                    'amount' => 20.0,
                    'rack_amount' => 40.0,
                    'type' => 'PropertyCollects',
                    'collected_by' => 'direct',
                    'code' => 'OBE_9',
                    'description' => 'Service Fee',
                    'obe_action' => 'add',
                    'is_commissionable' => true,
                    'MultiplierFee' => 2,
                    'displayable_amount' => 20.0,
                    'displayable_rack_amount' => 40.0,
                    'value_type' => 'Amount',
                ],
            ],
        ];

        // Act
        $actualBreakdown = $this->taxAndFeeResolver->getBreakdown($transformedRates);

        // Assert
        $this->assertEquals($expectedBreakdown, $actualBreakdown);
    }

    public function test_get_transformed_breakdown_with_tourism_tax_and_cleaning_fee(): void
    {
        // Arrange
        $transformedRates = [
            [
                'code' => '',
                'rate_time_unit' => 'Day',
                'unit_multiplier' => '3',
                'effective_date' => '2025-07-16',
                'expire_date' => '2025-07-19',
                'amount_before_tax' => '125.00',
                'amount_after_tax' => '125.00',
                'currency_code' => 'USD',
                'taxes' => [
                    [
                        'code' => 'OBE_5',
                        'description' => 'Tourism Tax',
                        'obe_action' => 'add',
                        'is_commissionable' => false,
                        'type' => 'Inclusive',
                        'collected_by' => 'vendor',
                        'amount' => 5.0,
                        'rack_amount' => 5.0,
                        'displayable_rack_amount' => 5.0,
                        'displayable_amount' => 5.0,
                        'value_type' => 'Amount',
                    ],
                ],
                'total_amount_before_tax' => '375.00',
                'total_amount_after_tax' => '375.00',
                'total_currency_code' => 'USD',
                'fees' => [
                    [
                        'code' => 'OBE_8',
                        'description' => 'Cleaning Fee',
                        'obe_action' => 'add',
                        'is_commissionable' => true,
                        'type' => 'Inclusive',
                        'collected_by' => 'vendor',
                        'amount' => 45.0,
                        'rack_amount' => 90.0,
                        'displayable_amount' => 45.0,
                        'displayable_rack_amount' => 90.0,
                        'value_type' => 'Amount',
                    ],
                ],
            ],
        ];

        $expectedBreakdown = [
            'nightly' => [
                [
                    [
                        'amount' => 120.00,
                        'rack_amount' => 120.00,
                        'type' => 'base_rate',
                        'collected_by' => 'vendor',
                        'title' => 'Base Rate',
                        'level' => 'rate',
                    ],
                    [
                        'amount' => 5.0,
                        'rack_amount' => 5.0,
                        'description' => 'Tourism Tax',
                        'type' => 'Inclusive',
                        'collected_by' => 'vendor',
                        'code' => 'OBE_5',
                        'obe_action' => 'add',
                        'is_commissionable' => false,
                        'displayable_rack_amount' => 5.0,
                        'displayable_amount' => 5.0,
                        'value_type' => 'Amount',
                    ],
                ],
                [
                    [
                        'amount' => 120.00,
                        'rack_amount' => 120.00,
                        'type' => 'base_rate',
                        'level' => 'rate',
                        'collected_by' => 'vendor',
                        'title' => 'Base Rate',
                    ],
                    [
                        'amount' => 5.0,
                        'rack_amount' => 5.0,
                        'description' => 'Tourism Tax',
                        'type' => 'Inclusive',
                        'collected_by' => 'vendor',
                        'code' => 'OBE_5',
                        'obe_action' => 'add',
                        'is_commissionable' => false,
                        'displayable_rack_amount' => 5.0,
                        'displayable_amount' => 5.0,
                        'value_type' => 'Amount',
                    ],
                ],
                [
                    [
                        'amount' => 120.00,
                        'rack_amount' => 120.00,
                        'type' => 'base_rate',
                        'level' => 'rate',
                        'collected_by' => 'vendor',
                        'title' => 'Base Rate',
                    ],
                    [
                        'amount' => 5.0,
                        'rack_amount' => 5.0,
                        'description' => 'Tourism Tax',
                        'type' => 'Inclusive',
                        'collected_by' => 'vendor',
                        'code' => 'OBE_5',
                        'obe_action' => 'add',
                        'is_commissionable' => false,
                        'displayable_rack_amount' => 5.0,
                        'displayable_amount' => 5.0,
                        'value_type' => 'Amount',
                    ],
                ],
            ],
            'stay' => [],
            'fees' => [
                [
                    'amount' => 45.0,
                    'rack_amount' => 90.0,
                    'type' => 'Inclusive',
                    'collected_by' => 'vendor',
                    'code' => 'OBE_8',
                    'obe_action' => 'add',
                    'is_commissionable' => true,
                    'displayable_amount' => 45.0,
                    'displayable_rack_amount' => 90.0,
                    'value_type' => 'Amount',
                    'description' => 'Cleaning Fee',
                ],
            ],
        ];

        // Act
        $actualBreakdown = $this->taxAndFeeResolver->getBreakdown($transformedRates);

        // Assert
        $this->assertEquals($expectedBreakdown, $actualBreakdown);
    }
}
