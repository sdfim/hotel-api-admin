<?php

namespace Tests\Unit\Modules\API\PricingAPI\Resolvers\TaxAndFees;

use Carbon\Carbon;
use Modules\API\PricingAPI\Resolvers\TaxAndFees\HbsiTaxAndFeeResolver;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Mandatory Taxes with Direct Collection
 *
 * This class tests all combinations of mandatory taxes where taxes are collected directly by the property.
 *
 * Coverage includes:
 * - Percentage/Amount value types
 * - per_room, per_person, per_night, per_night_per_person apply types
 */
class MandatoryTaxDirectCollectedTest extends BaseTaxAndFeeResolverTest
{
    public function test_tax_percentage_per_room_direct_collection(): void
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
                        'collected_by' => 'direct',
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
                    'type' => 'PropertyCollects',
                    'multiplier_fee' => '7',
                    'collected_by' => 'direct',
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
        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);

        // Assert
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_tax_percentage_per_person_direct_collection(): void
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
                        'apply_type' => 'per_person',
                        'commissionable' => false,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'direct',
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
                    'type' => 'PropertyCollects',
                    'multiplier_fee' => '2',
                    'collected_by' => 'direct',
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
        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);

        // Assert
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_tax_percentage_per_night_direct_collection(): void
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
                        'collected_by' => 'direct',
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
                    'type' => 'PropertyCollects',
                    'multiplier_fee' => '7',
                    'collected_by' => 'direct',
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

    public function test_tax_percentage_per_person_per_night_direct_collection(): void
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
                        'collected_by' => 'direct',
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
                    'type' => 'PropertyCollects',
                    'multiplier_fee' => '14',
                    'collected_by' => 'direct',
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

    // ============ TAX AMOUNT TESTS ============

    public function test_tax_amount_per_room_direct_collection(): void
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
                        'collected_by' => 'direct',
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
                    'type' => 'PropertyCollects',
                    'multiplier_fee' => '7',
                    'collected_by' => 'direct',
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
        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);

        // Assert
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_tax_amount_per_person_direct_collection(): void
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
                        'apply_type' => 'per_person',
                        'commissionable' => false,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'direct',
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
                    'type' => 'PropertyCollects',
                    'multiplier_fee' => '2',
                    'collected_by' => 'direct',
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
        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);

        // Assert
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_tax_amount_per_night_direct_collection(): void
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
                        'collected_by' => 'direct',
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
                    'type' => 'PropertyCollects',
                    'multiplier_fee' => '7',
                    'collected_by' => 'direct',
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

    public function test_tax_amount_per_person_per_night_direct_collection(): void
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
                        'apply_type' => 'per_night_per_person',
                        'commissionable' => false,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'direct',
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
                    'type' => 'PropertyCollects',
                    'multiplier_fee' => '14',
                    'collected_by' => 'direct',
                    'amount' => 140 / 7,
                    'rack_amount' => 140 / 7,
                    'value_type' => 'Amount',
                    'displayable_rack_amount' => 140 / 7,
                    'displayable_amount' => 140 / 7,
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

    public function test_edit_tax_direct_collection(): void
    {
        $baseNetRate = 125;
        $transformedRates = $this->createBaseTransformedRates($baseNetRate, 7);

        $transformedRates[0]['Taxes'] = [
            [
                'Code' => 'OBE_5',
                'Description' => 'Original Tax',
                'ObeAction' => 'add',
                'Type' => 'PropertyCollects',
                'Amount' => 10.0,
            ],
        ];

        $editInput = [
            1002 => [
                'edit' => [
                    6 => [
                        'id' => 6,
                        'product_id' => 2,
                        'room_id' => null,
                        'rate_id' => null,
                        'start_date' => '2025-06-10T00:00:00.000000Z',
                        'end_date' => null,
                        'supplier_id' => 2,
                        'action_type' => 'edit',
                        'old_name' => 'Original Tax',
                        'name' => 'Updated Tax',
                        'net_value' => 15.0,
                        'rack_value' => 15.0,
                        'type' => 'Tax',
                        'value_type' => 'Percentage',
                        'apply_type' => 'per_room',
                        'commissionable' => false,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'direct',
                        'rate_code' => null,
                        'unified_room_code' => null,
                        'currency' => 'USD',
                    ],
                ],
            ],
        ];

        $this->executeApplyRepoTaxFees($transformedRates, $editInput);
        $this->assertCount(1, $transformedRates[0]['Taxes']);
    }

    public function test_update_tax_direct_collection(): void
    {
        $baseNetRate = 125;
        $transformedRates = $this->createBaseTransformedRates($baseNetRate, 7);

        $transformedRates[0]['Taxes'] = [
            [
                'Code' => 'OBE_5',
                'Description' => 'Initial Tax',
                'ObeAction' => 'add',
                'Type' => 'PropertyCollects',
                'Amount' => 10.0,
            ],
        ];

        $updateInput = [
            1002 => [
                'update' => [
                    6 => [
                        'id' => 6,
                        'product_id' => 2,
                        'room_id' => null,
                        'rate_id' => null,
                        'start_date' => '2025-06-10T00:00:00.000000Z',
                        'end_date' => null,
                        'supplier_id' => 2,
                        'action_type' => 'update',
                        'old_name' => 'Initial Tax',
                        'name' => 'Updated Tax',
                        'net_value' => 20.0,
                        'rack_value' => 20.0,
                        'type' => 'Tax',
                        'value_type' => 'Percentage',
                        'apply_type' => 'per_room',
                        'commissionable' => false,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'direct',
                        'rate_code' => null,
                        'unified_room_code' => null,
                        'currency' => 'USD',
                    ],
                ],
            ],
        ];

        $this->executeApplyRepoTaxFees($transformedRates, $updateInput);
        $this->assertCount(1, $transformedRates[0]['Taxes']);
    }
}
