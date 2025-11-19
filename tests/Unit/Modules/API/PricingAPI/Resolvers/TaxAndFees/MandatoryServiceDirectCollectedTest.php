<?php

namespace Tests\Unit\Modules\API\PricingAPI\Resolvers\TaxAndFees;

use Carbon\Carbon;
use Modules\API\PricingAPI\Resolvers\TaxAndFees\HbsiTaxAndFeeResolver;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Mandatory Services with Direct Collection
 *
 * This class tests all combinations of mandatory services collected directly by the property.
 */
class MandatoryServiceDirectCollectedTest extends BaseTaxAndFeeResolverTest
{
    public function test_service_percentage_per_room_direct_collection(): void
    {
        $baseNetRate = 125;
        $nights = 1;
        $baseRackRate = 125;
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
                        'name' => 'Concierge Service',
                        'net_value' => 5.0,
                        'rack_value' => 5.0,
                        'type' => 'Fee',
                        'value_type' => 'Percentage',
                        'apply_type' => 'per_room',
                        'commissionable' => true,
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
                    'description' => 'Concierge Service',
                    'obe_action' => 'add',
                    'is_commissionable' => true,
                    'type' => 'PropertyCollects',
                    'collected_by' => 'direct',
                    'amount' => 6.25,
                    'rack_amount' => 6.25,
                    'value_type' => 'Percentage',
                    'displayable_rack_amount' => 6.25,
                    'displayable_amount' => 6.25,
                    'currency' => 'USD',
                ],
            ],
        ];

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput, $nights);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_service_percentage_per_person_direct_collection(): void
    {
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
                        'name' => 'Concierge Service',
                        'net_value' => 5.0,
                        'rack_value' => 5.0,
                        'type' => 'Fee',
                        'value_type' => 'Percentage',
                        'apply_type' => 'per_person',
                        'commissionable' => true,
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
                    'description' => 'Concierge Service',
                    'obe_action' => 'add',
                    'is_commissionable' => true,
                    'type' => 'PropertyCollects',
                    'collected_by' => 'direct',
                    'amount' => 12.5 / 2,
                    'rack_amount' => 12.5 / 2,
                    'value_type' => 'Percentage',
                    'displayable_rack_amount' => 12.5 / 2,
                    'displayable_amount' => 12.5 / 2,
                    'currency' => 'USD',
                ],
            ],
        ];

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput, $nights);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_service_percentage_per_night_direct_collection(): void
    {
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
                        'name' => 'Concierge Service',
                        'net_value' => 5.0,
                        'rack_value' => 5.0,
                        'type' => 'Fee',
                        'value_type' => 'Percentage',
                        'apply_type' => 'per_night',
                        'commissionable' => true,
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
                    'description' => 'Concierge Service',
                    'obe_action' => 'add',
                    'is_commissionable' => true,
                    'type' => 'PropertyCollects',
                    'collected_by' => 'direct',
                    'amount' => 43.75 / 7,
                    'rack_amount' => 43.75 / 7,
                    'value_type' => 'Percentage',
                    'displayable_rack_amount' => 43.75 / 7,
                    'displayable_amount' => 43.75 / 7,
                    'currency' => 'USD',
                ],
            ],
        ];

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput, $nights);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_service_percentage_per_person_per_night_direct_collection(): void
    {
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
                        'name' => 'Concierge Service',
                        'net_value' => 5.0,
                        'rack_value' => 5.0,
                        'type' => 'Fee',
                        'value_type' => 'Percentage',
                        'apply_type' => 'per_night_per_person',
                        'commissionable' => true,
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
                    'description' => 'Concierge Service',
                    'obe_action' => 'add',
                    'is_commissionable' => true,
                    'type' => 'PropertyCollects',
                    'collected_by' => 'direct',
                    'amount' => 87.5 / 14,
                    'rack_amount' => 87.5 / 14,
                    'value_type' => 'Percentage',
                    'displayable_rack_amount' => 87.5 / 14,
                    'displayable_amount' => 87.5 / 14,
                    'currency' => 'USD',
                ],
            ],
        ];

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput, $nights);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_service_amount_per_room_direct_collection(): void
    {
        $baseNetRate = 125;
        $nights = 7;
        $baseRackRate = 125;
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
                        'name' => 'Parking Service',
                        'net_value' => 15.0,
                        'rack_value' => 15.0,
                        'type' => 'Fee',
                        'value_type' => 'Amount',
                        'apply_type' => 'per_room',
                        'commissionable' => true,
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
                    'description' => 'Parking Service',
                    'obe_action' => 'add',
                    'is_commissionable' => true,
                    'type' => 'PropertyCollects',
                    'collected_by' => 'direct',
                    'amount' => 15,
                    'rack_amount' => 15,
                    'value_type' => 'Amount',
                    'displayable_rack_amount' => 15,
                    'displayable_amount' => 15,
                    'currency' => 'USD',
                ],
            ],
        ];

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput, $nights);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_service_amount_per_person_direct_collection(): void
    {
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
                        'name' => 'Parking Service',
                        'net_value' => 15.0,
                        'rack_value' => 15.0,
                        'type' => 'Fee',
                        'value_type' => 'Amount',
                        'apply_type' => 'per_person',
                        'commissionable' => true,
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
                    'description' => 'Parking Service',
                    'obe_action' => 'add',
                    'is_commissionable' => true,
                    'type' => 'PropertyCollects',
                    'collected_by' => 'direct',
                    'amount' => 30 / 2,
                    'rack_amount' => 30 / 2,
                    'value_type' => 'Amount',
                    'displayable_rack_amount' => 30 / 2,
                    'displayable_amount' => 30 / 2,
                    'currency' => 'USD',
                ],
            ],
        ];

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput, $nights);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_service_amount_per_night_direct_collection(): void
    {
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
                        'name' => 'Parking Service',
                        'net_value' => 15.0,
                        'rack_value' => 15.0,
                        'type' => 'Fee',
                        'value_type' => 'Amount',
                        'apply_type' => 'per_night',
                        'commissionable' => true,
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
                    'description' => 'Parking Service',
                    'obe_action' => 'add',
                    'is_commissionable' => true,
                    'type' => 'PropertyCollects',
                    'collected_by' => 'direct',
                    'amount' => 105 / 7,
                    'rack_amount' => 105 / 7,
                    'value_type' => 'Amount',
                    'displayable_rack_amount' => 105 / 7,
                    'displayable_amount' => 105 / 7,
                    'currency' => 'USD',
                ],
            ],
        ];

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput, $nights);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_service_amount_per_person_per_night_direct_collection(): void
    {
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
                        'name' => 'Parking Service',
                        'net_value' => 15.0,
                        'rack_value' => 15.0,
                        'type' => 'Fee',
                        'value_type' => 'Amount',
                        'apply_type' => 'per_night_per_person',
                        'commissionable' => true,
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
                    'description' => 'Parking Service',
                    'obe_action' => 'add',
                    'is_commissionable' => true,
                    'type' => 'PropertyCollects',
                    'collected_by' => 'direct',
                    'amount' => 210 / 14,
                    'rack_amount' => 210 / 14,
                    'value_type' => 'Amount',
                    'displayable_rack_amount' => 210 / 14,
                    'displayable_amount' => 210 / 14,
                    'currency' => 'USD',
                ],
            ],
        ];

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput, $nights);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_edit_service_direct_collection(): void
    {
        $baseNetRate = 125;
        $nights = 7;
        $transformedRates = $this->createBaseTransformedRates($baseNetRate, $nights);

        $transformedRates[0]['Fees'] = [
            [
                'Code' => 'OBE_5',
                'Description' => 'Original Service',
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
                        'old_name' => 'Original Service',
                        'name' => 'Updated Service',
                        'net_value' => 15.0,
                        'rack_value' => 15.0,
                        'type' => 'Fee',
                        'value_type' => 'Percentage',
                        'apply_type' => 'per_room',
                        'commissionable' => true,
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

    public function test_update_service_direct_collection(): void
    {
        $baseNetRate = 125;
        $nights = 7;
        $transformedRates = $this->createBaseTransformedRates($baseNetRate, $nights);

        $transformedRates[0]['Fees'] = [
            [
                'Code' => 'OBE_5',
                'Description' => 'Initial Service',
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
                        'old_name' => 'Initial Service',
                        'name' => 'Updated Service',
                        'net_value' => 20.0,
                        'rack_value' => 20.0,
                        'type' => 'Fee',
                        'value_type' => 'Percentage',
                        'apply_type' => 'per_room',
                        'commissionable' => true,
                        'fee_category' => 'mandatory',
                        'collected_by' => 'direct',
                        'rate_code' => null,
                        'unified_room_code' => null,
                        'currency' => 'USD',
                    ],
                ],
            ],
        ];

        $this->executeApplyRepoTaxFees($transformedRates, $updateInput, $nights);
        $this->assertCount(1, $transformedRates[0]['Fees']);
    }
}
