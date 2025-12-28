<?php

namespace Tests\Unit\Modules\API\PricingAPI\Resolvers\TaxAndFees;

/**
 * Test class for Mandatory Fees with Direct Collection
 *
 * This class tests all combinations of mandatory fees collected directly by the property.
 */
class MandatoryFeeDirectCollectedTest extends BaseTaxAndFeeResolverTest
{


    public function test_fee_percentage_per_room_direct_collection(): void
    {
        $baseNetRate = 125;
        $baseRackRate = 125;
        $transformedRates = $this->createBaseTransformedRates($baseNetRate, 1, $baseRackRate);

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
                        'name' => 'Service Fee',
                        'net_value' => 10.0,
                        'rack_value' => 10.0,
                        'type' => 'Fee',
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
            'taxes' => [],
            'fees' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'Service Fee',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'PropertyCollects',
                    'collected_by' => 'direct',
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

    public function test_fee_percentage_per_person_direct_collection(): void
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
                        'name' => 'Service Fee',
                        'net_value' => 10.0,
                        'rack_value' => 10.0,
                        'type' => 'Fee',
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
            'taxes' => [],
            'fees' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'Service Fee',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'PropertyCollects',
                    'collected_by' => 'direct',
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

    public function test_fee_percentage_per_night_direct_collection(): void
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
                        'name' => 'Service Fee',
                        'net_value' => 10.0,
                        'rack_value' => 10.0,
                        'type' => 'Fee',
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
            'taxes' => [],
            'fees' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'Service Fee',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'PropertyCollects',
                    'collected_by' => 'direct',
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

    public function test_fee_percentage_per_person_per_night_direct_collection(): void
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
                        'name' => 'Service Fee',
                        'net_value' => 10.0,
                        'rack_value' => 10.0,
                        'type' => 'Fee',
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
            'taxes' => [],
            'fees' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'Service Fee',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'PropertyCollects',
                    'collected_by' => 'direct',
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

    public function test_fee_amount_per_room_direct_collection(): void
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
                        'name' => 'Service Fee',
                        'net_value' => 10.0,
                        'rack_value' => 10.0,
                        'type' => 'Fee',
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
            'taxes' => [],
            'fees' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'Service Fee',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'PropertyCollects',
                    'collected_by' => 'direct',
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

    public function test_fee_amount_per_person_direct_collection(): void
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
                        'name' => 'Service Fee',
                        'net_value' => 10.0,
                        'rack_value' => 10.0,
                        'type' => 'Fee',
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
            'taxes' => [],
            'fees' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'Service Fee',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'PropertyCollects',
                    'collected_by' => 'direct',
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

    public function test_fee_amount_per_night_direct_collection(): void
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
                        'name' => 'Service Fee',
                        'net_value' => 10.0,
                        'rack_value' => 10.0,
                        'type' => 'Fee',
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
            'taxes' => [],
            'fees' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'Service Fee',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'PropertyCollects',
                    'collected_by' => 'direct',
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

    public function test_fee_amount_per_person_per_night_direct_collection(): void
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
                        'name' => 'Service Fee',
                        'net_value' => 10.0,
                        'rack_value' => 10.0,
                        'type' => 'Fee',
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
            'taxes' => [],
            'fees' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'Service Fee',
                    'obe_action' => 'add',
                    'is_commissionable' => false,
                    'type' => 'PropertyCollects',
                    'collected_by' => 'direct',
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

    public function test_edit_fee_direct_collection(): void
    {
        $baseNetRate = 125;
        $transformedRates = $this->createBaseTransformedRates($baseNetRate, 7);

        $transformedRates[0]['Fees'] = [
            [
                'Code' => 'OBE_5',
                'Description' => 'Original Fee',
                'ObeAction' => 'add',
                'Type' => 'PropertyCollects',
                'Amount' => 10.0,
                'Currency' => 'USD',
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
                        'old_name' => 'Original Fee',
                        'name' => 'Updated Fee',
                        'net_value' => 15.0,
                        'rack_value' => 15.0,
                        'type' => 'Fee',
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
        $this->assertCount(1, $transformedRates[0]['Fees']);
    }

    public function test_update_fee_direct_collection(): void
    {
        $baseNetRate = 125;
        $transformedRates = $this->createBaseTransformedRates($baseNetRate, 7);

        $transformedRates[0]['Fees'] = [
            [
                'Code' => 'OBE_5',
                'Description' => 'Initial Fee',
                'ObeAction' => 'add',
                'Type' => 'PropertyCollects',
                'Amount' => 10.0,
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
                        'net_value' => 20.0,
                        'rack_value' => 20.0,
                        'type' => 'Fee',
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
        $this->assertCount(1, $transformedRates[0]['Fees']);
    }
}
