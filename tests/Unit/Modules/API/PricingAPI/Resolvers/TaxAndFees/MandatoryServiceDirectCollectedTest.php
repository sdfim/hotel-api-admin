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
class MandatoryServiceDirectCollectedTest extends TestCase
{
    private HbsiTaxAndFeeResolver $taxAndFeeResolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->taxAndFeeResolver = new HbsiTaxAndFeeResolver;
    }

    private function createBaseTransformedRates(float $baseNetRate, int $nights = 1, float $baseRackRate = 0): array
    {
        if ($baseRackRate === 0) {
            $baseRackRate = $baseNetRate;
        }

        $checkinInput = Carbon::parse('2025-08-10')->startOfDay()->toDateString();
        $checkoutInput = Carbon::parse('2025-08-10')->startOfDay()->addDays($nights)->toDateString();

        return [
            [
                'code' => '',
                'rate_time_unit' => 'Day',
                'unit_multiplier' => $nights,
                'effective_date' => $checkinInput,
                'expire_date' => $checkoutInput,
                'amount_before_tax' => $baseNetRate,
                'amount_after_tax' => $baseNetRate,
                'currency_code' => 'USD',
                'taxes' => [],
                'total_amount_before_tax' => $baseNetRate * $nights,
                'totalAmount-after_tax' => $baseNetRate * $nights,
                'total_currency_code' => 'USD',
            ],
        ];
    }

    private function executeApplyRepoTaxFees(array &$transformedRates, array $repoTaxFeesInput, int $numberOfPassengers = 2): void
    {
        $giataCode = 1002;
        $ratePlanCode = 'Loyalty';
        $unifiedRoomCode = '';
        $checkinInput = Carbon::parse('2025-08-10')->startOfDay()->toDateString();
        $nights = 7;
        $checkoutInput = Carbon::parse('2025-08-17')->startOfDay()->addDays($nights)->toDateString();
        $occupancy = [['adults' => 2]];

        $this->taxAndFeeResolver->applyRepoTaxFees(
            $transformedRates, $giataCode, $ratePlanCode, $unifiedRoomCode,
            $numberOfPassengers, $checkinInput, $checkoutInput, $repoTaxFeesInput, $occupancy
        );
    }

    private function assertTaxAndFeeResults(array $transformedRates, array $expectedResult): void
    {
        foreach ($expectedResult as $expectedItemKey => $expectedItemValue) {
            if (is_array($expectedItemValue)) {
                foreach ($expectedItemValue as $index => $item) {
                    foreach ($item as $key => $value) {
                        if (is_numeric($value)) {
                            $this->assertEqualsWithDelta($value, $transformedRates[0][$expectedItemKey][$index][$key], 0.01);
                        } else {
                            $this->assertEquals($value, $transformedRates[0][$expectedItemKey][$index][$key]);
                        }
                    }
                }
            } else {
                $this->assertEquals($expectedItemValue, $transformedRates[0][$expectedItemKey]);
            }
        }
    }

    public function test_service_percentage_per_room_direct_collection(): void
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
                    'multiplier_fee' => '1',
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

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_service_percentage_per_person_direct_collection(): void
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
                    'multiplier_fee' => '2',
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

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_service_percentage_per_night_direct_collection(): void
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
                    'multiplier_fee' => '7',
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

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_service_percentage_per_person_per_night_direct_collection(): void
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
                    'multiplier_fee' => '14',
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

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_service_amount_per_room_direct_collection(): void
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
                    'multiplier_fee' => '1',
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

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_service_amount_per_person_direct_collection(): void
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
                    'multiplier_fee' => '2',
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

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_service_amount_per_night_direct_collection(): void
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
                    'multiplier_fee' => '7',
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

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_service_amount_per_person_per_night_direct_collection(): void
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
                    'multiplier_fee' => '14',
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

        $this->executeApplyRepoTaxFees($transformedRates, $repoTaxFeesInput);
        $this->assertTaxAndFeeResults($transformedRates, $expectedResult);
    }

    public function test_edit_service_direct_collection(): void
    {
        $baseNetRate = 125;
        $transformedRates = $this->createBaseTransformedRates($baseNetRate, 7);

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
        $transformedRates = $this->createBaseTransformedRates($baseNetRate, 7);

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

        $this->executeApplyRepoTaxFees($transformedRates, $updateInput);
        $this->assertCount(1, $transformedRates[0]['Fees']);
    }
}
