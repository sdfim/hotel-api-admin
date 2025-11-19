<?php

namespace Tests\Unit\Modules\API\PricingAPI\Resolvers\TaxAndFees;

/**
 * Test class for Mandatory Fees with Vendor Collection
 *
 * This class tests all combinations of mandatory fees where fees are collected by the vendor.
 */
class MandatoryFeeVendorCollectedTest extends BaseTaxAndFeeResolverTest
{
    public function test_fee_percentage_per_room(): void
    {
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
                        'name' => 'Resort Fee',
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
            'taxes' => [],
            'fees' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'Resort Fee',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 12.5,
                    'rack_amount' => 12.5,
                    'value_type' => 'Percentage',
                    'displayable_rack_amount' => 12.5,
                    'displayable_amount' => 12.5,
                    'currency' => 'USD',
                ],
            ],
        ];

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_fee_percentage_per_person(): void
    {
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
                        'name' => 'Resort Fee',
                        'net_value' => 10.0,
                        'rack_value' => 10.0,
                        'type' => 'Fee',
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
            'taxes' => [],
            'fees' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'Resort Fee',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 25 / 2,
                    'rack_amount' => 25 / 2,
                    'value_type' => 'Percentage',
                    'displayable_rack_amount' => 25 / 2,
                    'displayable_amount' => 25 / 2,
                    'currency' => 'USD',
                ],
            ],
        ];

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_fee_percentage_per_night(): void
    {
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
                        'name' => 'Resort Fee',
                        'net_value' => 10.0,
                        'rack_value' => 10.0,
                        'type' => 'Fee',
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
            'taxes' => [],
            'fees' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'Resort Fee',
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

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_fee_percentage_per_person_per_night(): void
    {
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
                        'name' => 'Resort Fee',
                        'net_value' => 10.0,
                        'rack_value' => 10.0,
                        'type' => 'Fee',
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
            'taxes' => [],
            'fees' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'Resort Fee',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 175 / 14,
                    'rack_amount' => 175 / 14,
                    'value_type' => 'Percentage',
                    'displayable_rack_amount' => 175 / 14,
                    'displayable_amount' => 175 / 14,
                    'currency' => 'USD',
                ],
            ],
        ];

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_fee_amount_per_room(): void
    {
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
                        'name' => 'Resort Fee',
                        'net_value' => 10.0,
                        'rack_value' => 10.0,
                        'type' => 'Fee',
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
            'taxes' => [],
            'fees' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'Resort Fee',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 10,
                    'rack_amount' => 10,
                    'value_type' => 'Amount',
                    'displayable_rack_amount' => 10,
                    'displayable_amount' => 10,
                    'currency' => 'USD',
                ],
            ],
        ];

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_fee_amount_per_person(): void
    {
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
                        'name' => 'Resort Fee',
                        'net_value' => 10.0,
                        'rack_value' => 10.0,
                        'type' => 'Fee',
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
            'taxes' => [],
            'fees' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'Resort Fee',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 20 / 2,
                    'rack_amount' => 20 / 2,
                    'value_type' => 'Amount',
                    'displayable_rack_amount' => 20 / 2,
                    'displayable_amount' => 20 / 2,
                    'currency' => 'USD',
                ],
            ],
        ];

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_fee_amount_per_night(): void
    {
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
                        'name' => 'Resort Fee',
                        'net_value' => 10.0,
                        'rack_value' => 10.0,
                        'type' => 'Fee',
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
            'taxes' => [],
            'fees' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'Resort Fee',
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
        ];

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_fee_amount_per_person_per_night(): void
    {
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
                        'name' => 'Resort Fee',
                        'net_value' => 10.0,
                        'rack_value' => 10.0,
                        'type' => 'Fee',
                        'value_type' => 'Amount',
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
            'taxes' => [],
            'fees' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'Resort Fee',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 140 / 14,
                    'rack_amount' => 140 / 14,
                    'value_type' => 'Amount',
                    'displayable_rack_amount' => 140 / 14,
                    'displayable_amount' => 140 / 14,
                    'currency' => 'USD',
                ],
            ],
        ];

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    // ============ EDIT OPERATIONS ============

    public function test_edit_fee_percentage_per_room(): void
    {
        // Start with an existing fee in transformedRates
        $baseNetRate = 125;
        $baseRackRate = 125;
        $transformedRates = $this->createBaseTransformedRates($baseNetRate, 7, $baseRackRate);

        // Manually add a fee first
        $transformedRates[0]['Fees'] = [
            [
                'Code' => 'OBE_5',
                'Description' => 'Original Fee',
                'ObeAction' => 'add',
                'IsCommissionable' => false,
                'Type' => 'Exclusive',
                'CollectedBy' => 'Vendor',
                'Amount' => 10.0,
                'RackAmount' => 10.0,
                'ValueType' => 'Percentage',
                'DisplayableRackAmount' => 10.0,
                'DisplayableAmount' => 10.0,
                'Currency' => 'USD',
            ],
        ];

        // Now edit it
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
                        'old_name' => 'Original Fee',
                        'name' => 'Updated Fee',
                        'net_value' => 15.0,
                        'rack_value' => 15.0,
                        'type' => 'Fee',
                        'value_type' => 'Percentage',
                        'apply_type' => 'per_room',
                        'commissionable' => false,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'vendor',
                        'rate_code' => null,
                        'unified_room_code' => null,
                        'currency' => 'USD',
                    ],
                ],
            ],
        ];

        $this->executeApplyRepoTaxFees($transformedRates, $editInput);

        // Should still have 1 fee
        $this->assertCount(1, $transformedRates[0]['Fees']);
    }

    // ============ UPDATE OPERATIONS ============

    public function test_update_fee_amount_per_person(): void
    {
        $baseNetRate = 125;
        $transformedRates = $this->createBaseTransformedRates($baseNetRate, 7);

        // Manually add a fee
        $transformedRates[0]['Fees'] = [
            [
                'Code' => 'OBE_5',
                'Description' => 'Initial Fee',
                'ObeAction' => 'add',
                'Type' => 'Exclusive',
                'Amount' => 20.0,
                'Currency' => 'USD',
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
                        'old_name' => 'Initial Fee',
                        'name' => 'Updated Fee',
                        'net_value' => 30.0,
                        'rack_value' => 30.0,
                        'type' => 'Fee',
                        'value_type' => 'Amount',
                        'apply_type' => 'per_person',
                        'commissionable' => true,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'vendor',
                        'rate_code' => null,
                        'unified_room_code' => null,
                        'currency' => 'USD',
                    ],
                ],
            ],
        ];

        $this->executeApplyRepoTaxFees($transformedRates, $updateInput);
        $this->assertCount(1, $transformedRates[0]['Fees']);
    }
}
