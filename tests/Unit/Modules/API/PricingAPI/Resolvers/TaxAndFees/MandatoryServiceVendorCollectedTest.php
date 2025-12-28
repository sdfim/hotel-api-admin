<?php

namespace Tests\Unit\Modules\API\PricingAPI\Resolvers\TaxAndFees;

/**
 * Test class for Mandatory Services with Vendor Collection
 *
 * Note: Services are typically handled by ServiceResolver, but fees of type "Service"
 * are processed through TaxAndFeeResolver. This class tests all combinations of
 * mandatory services collected by the vendor.
 *
 * Services use the same logic as Fees but may have different business rules.
 */
class MandatoryServiceVendorCollectedTest extends BaseTaxAndFeeResolverTest
{
    public function test_service_percentage_per_room(): void
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
                        'name' => 'WiFi Service',
                        'net_value' => 5.0,
                        'rack_value' => 5.0,
                        'type' => 'Fee',
                        'value_type' => 'Percentage',
                        'apply_type' => 'per_room',
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
            'taxes' => [],
            'fees' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'WiFi Service',
                    'obe_action' => 'add',
                    'is_commissionable' => true,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 6.25,
                    'rack_amount' => 6.25,
                    'value_type' => 'Percentage',
                    'displayable_rack_amount' => 6.25,
                    'displayable_amount' => 6.25,
                    'currency' => 'USD',
                ],
            ],
        ];

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_service_percentage_per_person(): void
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
                        'name' => 'WiFi Service',
                        'net_value' => 5.0,
                        'rack_value' => 5.0,
                        'type' => 'Fee',
                        'value_type' => 'Percentage',
                        'apply_type' => 'per_person',
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
            'taxes' => [],
            'fees' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'WiFi Service',
                    'obe_action' => 'add',
                    'is_commissionable' => true,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 12.5 / 2,
                    'rack_amount' => 12.5 / 2,
                    'value_type' => 'Percentage',
                    'displayable_rack_amount' => 12.5 / 2,
                    'displayable_amount' => 12.5 / 2,
                    'currency' => 'USD',
                ],
            ],
        ];

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_service_percentage_per_night(): void
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
                        'name' => 'WiFi Service',
                        'net_value' => 5.0,
                        'rack_value' => 5.0,
                        'type' => 'Fee',
                        'value_type' => 'Percentage',
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
            'taxes' => [],
            'fees' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'WiFi Service',
                    'obe_action' => 'add',
                    'is_commissionable' => true,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 43.75 / 7,
                    'rack_amount' => 43.75 / 7,
                    'value_type' => 'Percentage',
                    'displayable_rack_amount' => 43.75 / 7,
                    'displayable_amount' => 43.75 / 7,
                    'currency' => 'USD',
                ],
            ],
        ];

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_service_percentage_per_person_per_night(): void
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
                        'name' => 'WiFi Service',
                        'net_value' => 5.0,
                        'rack_value' => 5.0,
                        'type' => 'Fee',
                        'value_type' => 'Percentage',
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
            'taxes' => [],
            'fees' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'WiFi Service',
                    'obe_action' => 'add',
                    'is_commissionable' => true,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 87.5 / 14,
                    'rack_amount' => 87.5 / 14,
                    'value_type' => 'Percentage',
                    'displayable_rack_amount' => 87.5 / 14,
                    'displayable_amount' => 87.5 / 14,
                    'currency' => 'USD',
                ],
            ],
        ];

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_service_amount_per_room(): void
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
                        'name' => 'Airport Shuttle',
                        'net_value' => 25.0,
                        'rack_value' => 25.0,
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
                ],
            ],
        ];

        $expectedResult = [
            'taxes' => [],
            'fees' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'Airport Shuttle',
                    'obe_action' => 'add',
                    'is_commissionable' => true,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 25,
                    'rack_amount' => 25,
                    'value_type' => 'Amount',
                    'displayable_rack_amount' => 25,
                    'displayable_amount' => 25,
                    'currency' => 'USD',
                ],
            ],
        ];

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_service_amount_per_person(): void
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
                        'name' => 'Airport Shuttle',
                        'net_value' => 25.0,
                        'rack_value' => 25.0,
                        'type' => 'Fee',
                        'value_type' => 'Amount',
                        'apply_type' => 'per_person',
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
            'taxes' => [],
            'fees' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'Airport Shuttle',
                    'obe_action' => 'add',
                    'is_commissionable' => true,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 50 / 2,
                    'rack_amount' => 50 / 2,
                    'value_type' => 'Amount',
                    'displayable_rack_amount' => 50 / 2,
                    'displayable_amount' => 50 / 2,
                    'currency' => 'USD',
                ],
            ],
        ];

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_service_amount_per_night(): void
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
                        'name' => 'Airport Shuttle',
                        'net_value' => 25.0,
                        'rack_value' => 25.0,
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
            'taxes' => [],
            'fees' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'Airport Shuttle',
                    'obe_action' => 'add',
                    'is_commissionable' => true,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 175 / 7,
                    'rack_amount' => 175 / 7,
                    'value_type' => 'Amount',
                    'displayable_rack_amount' => 175 / 7,
                    'displayable_amount' => 175 / 7,
                    'currency' => 'USD',
                ],
            ],
        ];

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_service_amount_per_person_per_night(): void
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
                        'name' => 'Airport Shuttle',
                        'net_value' => 25.0,
                        'rack_value' => 25.0,
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
            'taxes' => [],
            'fees' => [
                [
                    'code' => 'OBE_5',
                    'description' => 'Airport Shuttle',
                    'obe_action' => 'add',
                    'is_commissionable' => true,
                    'type' => 'Exclusive',
                    'collected_by' => 'Vendor',
                    'amount' => 350 / 14,
                    'rack_amount' => 350 / 14,
                    'value_type' => 'Amount',
                    'displayable_rack_amount' => 350 / 14,
                    'displayable_amount' => 350 / 14,
                    'currency' => 'USD',
                ],
            ],
        ];

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_edit_service_vendor_collection(): void
    {
        $baseNetRate = 125;
        $transformedRates = $this->createBaseTransformedRates($baseNetRate, 7);

        $transformedRates[0]['fees'] = [
            [
                'code' => 'OBE_5',
                'description' => 'Original Service',
                'obe_action' => 'add',
                'type' => 'Exclusive',
                'amount' => 10.0,
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
                        'old_name' => 'Original Service',
                        'name' => 'Updated Service',
                        'net_value' => 15.0,
                        'rack_value' => 15.0,
                        'type' => 'Fee',
                        'value_type' => 'Percentage',
                        'apply_type' => 'per_room',
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

        $this->executeApplyRepoTaxFees($transformedRates, $editInput);
        $this->assertCount(1, $transformedRates[0]['fees']);
    }

    public function test_update_service_vendor_collection(): void
    {
        $baseNetRate = 125;
        $transformedRates = $this->createBaseTransformedRates($baseNetRate, 7);

        $transformedRates[0]['fees'] = [
            [
                'code' => 'OBE_5',
                'description' => 'Initial Service',
                'obe_action' => 'add',
                'type' => 'Exclusive',
                'amount' => 10.0,
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
                        'old_name' => 'Initial Service',
                        'name' => 'Updated Service',
                        'net_value' => 20.0,
                        'rack_value' => 20.0,
                        'type' => 'Fee',
                        'value_type' => 'Percentage',
                        'apply_type' => 'per_room',
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

        $this->executeApplyRepoTaxFees($transformedRates, $updateInput);
        $this->assertCount(1, $transformedRates[0]['fees']);
    }
}
