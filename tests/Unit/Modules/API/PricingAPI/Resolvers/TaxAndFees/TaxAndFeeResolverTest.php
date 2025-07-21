<?php

namespace Tests\Unit\Modules\API\PricingAPI\Resolvers\TaxAndFees;

use Carbon\Carbon;
use Modules\API\PricingAPI\Resolvers\TaxAndFees\TaxAndFeeResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Class TaxAndFeeResolverTest
 *
 * This class tests the TaxAndFeeResolver functionality.
 *
 * @todo Get data from the database instead of hardcoding it.
 */
class TaxAndFeeResolverTest extends TestCase
{
    /**
     * @var TaxAndFeeResolver
     *
     */
    private TaxAndFeeResolver $taxAndFeeResolver;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->taxAndFeeResolver = new TaxAndFeeResolver();
    }

    #[DataProvider('applyRepoTaxFeesDataProvider')]
    public function testApplyRepoTaxFees(
        float $baseNetRate,
        array $repoTaxFeesInput,
        array $expectedResult,
    ): void {
        // Arrange
        $giataCode = 1002;
        $ratePlanCode = "Loyalty";
        $unifiedRoomCode = '';
        $checkinInput = Carbon::parse('2025-08-10')->startOfDay()->toDateString();
        $nights = 1;
        $checkoutInput = Carbon::parse('2025-08-10')->startOfDay()->addDays($nights)->toDateString();
        $numberOfPassengers = 2;
        $transformedRates = [
            [
                "Code" => "",
                "RateTimeUnit" => "Day",
                "UnitMultiplier" => "1",
                "EffectiveDate" => $checkinInput,
                "ExpireDate" => $checkoutInput,
                "AmountBeforeTax" => $baseNetRate,
                "AmountAfterTax" => $baseNetRate,
                "CurrencyCode" => "USD",
                "Taxes" => [],
                "TotalAmountBeforeTax" => $baseNetRate * $nights,
                "TotalAmountAfterTax" => $baseNetRate * $nights,
                "TotalCurrencyCode" => "USD",
            ],
        ];

        // Act
        $this->taxAndFeeResolver->applyRepoTaxFees(
            $transformedRates,
            $giataCode,
            $ratePlanCode,
            $unifiedRoomCode,
            $numberOfPassengers,
            $checkinInput,
            $checkoutInput,
            $repoTaxFeesInput
        );

        // Assert
        foreach ($expectedResult as $expectedItemKey => $expectedItemValue) {
            if (is_array($expectedItemValue)) {
                foreach ($expectedItemValue as $index => $item) {
                    foreach ($item as $key => $value) {
                        if (is_numeric($value)) {
                            $this->assertEqualsWithDelta($value, $transformedRates[0][$expectedItemKey][$index][$key], 0.0001);
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

    /**
     * Data provider for testApplyRepoTaxFees.
     *
     * @return array
     */
    public static function applyRepoTaxFeesDataProvider(): array
    {
        return [
            'Test Case 1: Single Percentage Tax on Net Rate' => [
                'baseNetRate' => 200.00,
                'repoTaxFeesInput' => [
                    1002 => [
                        "add" => [
                            5 => [
                                "id" => 5,
                                "product_id" => 2,
                                "room_id" => null,
                                "rate_id" => null,
                                "start_date" => "2025-06-10T00:00:00.000000Z",
                                "end_date" => null,
                                "supplier_id" => 2,
                                "action_type" => "add",
                                "old_name" => null,
                                "name" => "VAT",
                                "net_value" => 10.0,
                                "rack_value" => 10.0,
                                "type" => "Tax",
                                "value_type" => "Percentage",
                                "apply_type" => "per_room",
                                "commissionable" => false,
                                "fee_category" => "mandatory",
                                "collected_by" => "Direct",
                                "rate_code" => null,
                                "unified_room_code" => null,
                            ],
                        ],
                    ],
                ],
                'expectedResult' => [
                    'Taxes' => [
                        [
                            'Code' => 'OBE_5',
                            'Description' => 'VAT',
                            'ObeAction' => 'add',
                            'IsCommissionable' => false,
                            'Type' => 'PropertyCollects',
                            'MultiplierFee' => '1',
                            'CollectedBy' => 'direct',
                            'Amount' => 18.181818181818187,
                            'RackAmount' => 18.181818181818187,
                            'ValueType' => 'Percentage',
                            'DisplayableRackAmount' => 18.18,
                            'DisplayableAmount' => 18.18,
                        ],
                    ],
                    'Fees' => [],
                ],
            ],

            'Test Case 2: Multiple Percentage Taxes on Net Rate' => [
                'baseNetRate' => 250.00,
                'repoTaxFeesInput' => [
                    1002 => [
                        "add" => [
                            5 => [ // VAT
                                "id" => 5,
                                "product_id" => 2,
                                "room_id" => null,
                                "rate_id" => null,
                                "start_date" => "2025-06-10T00:00:00.000000Z",
                                "end_date" => null,
                                "supplier_id" => 2,
                                "action_type" => "add",
                                "old_name" => null,
                                "name" => "VAT",
                                "net_value" => 16.0,
                                "rack_value" => 16.0,
                                "type" => "Tax",
                                "value_type" => "Percentage",
                                "apply_type" => "per_room",
                                "commissionable" => false,
                                "fee_category" => "mandatory",
                                "collected_by" => "Direct",
                                "rate_code" => null,
                                "unified_room_code" => null,
                            ],
                            6 => [ // Service Fee
                                "id" => 6,
                                "product_id" => 2,
                                "room_id" => null,
                                "rate_id" => null,
                                "start_date" => "2025-06-10T00:00:00.000000Z",
                                "end_date" => null,
                                "supplier_id" => 2,
                                "action_type" => "add",
                                "old_name" => null,
                                "name" => "Service Fee",
                                "net_value" => 10.0,
                                "rack_value" => 10.0,
                                "type" => "Fee", // Assuming Service Fee is a 'Fee' type
                                "value_type" => "Percentage",
                                "apply_type" => "per_room",
                                "commissionable" => false,
                                "fee_category" => "mandatory",
                                "collected_by" => "Direct",
                                "rate_code" => null,
                                "unified_room_code" => null,
                            ],
                        ],
                    ],
                ],
                'expectedResult' => [
                    'Taxes' => [
                        [
                            'Code' => 'OBE_5',
                            'Description' => 'VAT',
                            'ObeAction' => 'add',
                            'IsCommissionable' => false,
                            'Type' => 'PropertyCollects',
                            'MultiplierFee' => '1',
                            'CollectedBy' => 'direct',
                            'ValueType' => 'Percentage',
                            'Amount' => 34.48275862068965,
                            'RackAmount' => 34.48275862068965,
                            'DisplayableRackAmount' => 34.48,
                            'DisplayableAmount' => 34.48,
                        ],
                    ],
                    'Fees' => [
                        [
                            'Code' => 'OBE_6',
                            'Description' => 'Service Fee',
                            'ObeAction' => 'add',
                            'IsCommissionable' => false,
                            'Type' => 'PropertyCollects',
                            'MultiplierFee' => 1,
                            'CollectedBy' => 'direct',
                            'ValueType' => 'Percentage',
                            'Amount' => 25.0,
                            'RackAmount' => 25.0,
                            'DisplayableRackAmount' => 25.0,
                            'DisplayableAmount' => 25.0,
                        ],
                    ],
                ],
            ],

            'Test Case 3: Zero Percent Tax on Net Rate' => [
                'baseNetRate' => 150.00,
                'repoTaxFeesInput' => [
                    1002 => [
                        "add" => [
                            7 => [ // Promotional Tax
                                "id" => 7,
                                "product_id" => 2,
                                "room_id" => null,
                                "rate_id" => null,
                                "start_date" => "2025-06-10T00:00:00.000000Z",
                                "end_date" => null,
                                "supplier_id" => 2,
                                "action_type" => "add",
                                "old_name" => null,
                                "name" => "Promotional Tax",
                                "net_value" => 0.0,
                                "rack_value" => 0.0,
                                "type" => "Tax",
                                "value_type" => "Percentage",
                                "apply_type" => "per_room",
                                "commissionable" => false,
                                "fee_category" => "mandatory",
                                "collected_by" => "Direct",
                                "rate_code" => null,
                                "unified_room_code" => null,
                            ],
                        ],
                    ],
                ],
                'expectedResult' => [
                    'Taxes' => [
                        [
                            'Code' => 'OBE_7',
                            'Description' => 'Promotional Tax',
                            'ObeAction' => 'add',
                            'IsCommissionable' => false,
                            'Type' => 'PropertyCollects',
                            'MultiplierFee' => '1',
                            'CollectedBy' => 'direct',
                            'Amount' => 0.0,
                            'RackAmount' => 0.0,
                            'ValueType' => 'Percentage',
                            'DisplayableRackAmount' => 0.0,
                            'DisplayableAmount' => 0.0,
                        ],
                    ],
                    'Fees' => [], // No fees in this test case
                ],
            ],

            'Test Case 4: 100% Tax on Net Rate' => [
                'baseNetRate' => 400.00,
                'repoTaxFeesInput' => [
                    1002 => [
                        "add" => [
                            8 => [ // Special Surcharge
                                "id" => 8,
                                "product_id" => 2,
                                "room_id" => null,
                                "rate_id" => null,
                                "start_date" => "2025-06-10T00:00:00.000000Z",
                                "end_date" => null,
                                "supplier_id" => 2,
                                "action_type" => "add",
                                "old_name" => null,
                                "name" => "Special Surcharge",
                                "net_value" => 100.0,
                                "rack_value" => 100.0,
                                "type" => "Tax", // Assuming it's a 'Tax' for this scenario
                                "value_type" => "Percentage",
                                "apply_type" => "per_room",
                                "commissionable" => false,
                                "fee_category" => "mandatory",
                                "collected_by" => "Direct",
                                "rate_code" => null,
                                "unified_room_code" => null,
                            ],
                        ],
                    ],
                ],
                'expectedResult' => [
                    'Taxes' => [
                        [
                            'Code' => 'OBE_8',
                            'Description' => 'Special Surcharge',
                            'ObeAction' => 'add',
                            'IsCommissionable' => false,
                            'Type' => 'PropertyCollects',
                            'MultiplierFee' => '1',
                            'CollectedBy' => 'direct',
                            'ValueType' => 'Percentage',
                            'Amount' => 200.0,
                            'RackAmount' => 200.0,
                            'DisplayableRackAmount' => 200.0,
                            'DisplayableAmount' => 200.0,
                        ],
                    ],
                    'Fees' => [], // No fees in this test case
                ],
            ],

            'Test Case 5: City Tax, Resort Fee, and Service Fee' => [
                'baseNetRate' => 125.00,
                'repoTaxFeesInput' => [
                    1002 => [
                        "add" => [
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
                                'name' => 'City Tax ',
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
                            ],
                        ],
                    ],
                ],
                'expectedResult' => [
                    'Taxes' => [
                        [
                            'Code' => 'OBE_5',
                            'Description' => 'City Tax ',
                            'ObeAction' => 'add',
                            'IsCommissionable' => false,
                            'Type' => 'PropertyCollects',
                            'MultiplierFee' => '1',
                            'CollectedBy' => 'direct',
                            'Amount' => 5.952380952380963,
                            'RackAmount' => 5.952380952380963,
                            'ValueType' => 'Percentage',
                            'DisplayableRackAmount' => 5.95,
                            'DisplayableAmount' => 5.95,
                        ],
                    ],
                    'Fees' => [
                        [
                            'Code' => 'OBE_8',
                            'Description' => 'Resort Fee',
                            'ObeAction' => 'add',
                            'IsCommissionable' => true,
                            'Type' => 'Inclusive',
                            'MultiplierFee' => 1,
                            'CollectedBy' => 'vendor',
                            'Amount' => 30.0,
                            'RackAmount' => 40.0,
                            'ValueType' => 'Amount',
                            'DisplayableRackAmount' => 30.0,
                            'DisplayableAmount' => 30.0,
                        ],
                        [
                            'Code' => 'OBE_9',
                            'Description' => 'Service Fee',
                            'ObeAction' => 'add',
                            'IsCommissionable' => true,
                            'Type' => 'PropertyCollects',
                            'MultiplierFee' => 2,
                            'CollectedBy' => 'direct',
                            'Amount' => 20.0,
                            'RackAmount' => 40.0,
                            'ValueType' => 'Amount',
                            'DisplayableRackAmount' => 20.0,
                            'DisplayableAmount' => 20.0,
                        ],
                    ],
                ],
            ],

            'Test Case 6: Tourism Tax and Cleaning Fee' => [
                'baseNetRate' => 150.00,
                'repoTaxFeesInput' => [
                    1002 => [
                        "add" => [
                            5 => [
                                'id' => 5,
                                'product_id' => 2,
                                'room_id' => null,
                                'rate_id' => null,
                                'start_date' => "2025-06-10T00:00:00.000000Z",
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
                            ],
                        ],
                    ],
                ],
                'expectedResult' => [
                    'Taxes' => [
                        [
                            'Code' => 'OBE_5',
                            'Description' => 'Tourism Tax',
                            'ObeAction' => 'add',
                            'IsCommissionable' => false,
                            'Type' => 'PropertyCollects',
                            'MultiplierFee' => '1',
                            'CollectedBy' => 'direct',
                            'Amount' => 5.0,
                            'RackAmount' => 5.0,
                            'ValueType' => 'Amount',
                            'DisplayableRackAmount' => 5.0,
                            'DisplayableAmount' => 5.0,
                        ],
                    ],
                    'Fees' => [
                        [
                            'Code' => 'OBE_8',
                            'Description' => 'Cleaning Fee',
                            'ObeAction' => 'add',
                            'IsCommissionable' => true,
                            'Type' => 'Inclusive',
                            'MultiplierFee' => 1,
                            'CollectedBy' => 'vendor',
                            'Amount' => 15.0,
                            'RackAmount' => 30.0,
                            'ValueType' => 'Amount',
                            'DisplayableRackAmount' => 15.0,
                            'DisplayableAmount' => 15.0,
                        ],
                    ],
                ],
            ],

            'Test Case 7: Occupancy Tax and Environmental Fee' => [
                'baseNetRate' => 175.00,
                'repoTaxFeesInput' => [
                    1002 => [
                        "add" => [
                            5 => [
                                'id' => 5,
                                'product_id' => 2,
                                'room_id' => null,
                                'rate_id' => null,
                                'start_date' => "2025-06-10T00:00:00.000000Z",
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
                            ],
                        ],
                    ],
                ],
                'expectedResult' => [
                    'Taxes' => [
                        [
                            'Code' => 'OBE_5',
                            'Description' => 'Occupancy Tax',
                            'ObeAction' => 'add',
                            'IsCommissionable' => false,
                            'Type' => 'PropertyCollects',
                            'MultiplierFee' => '1',
                            'CollectedBy' => 'direct',
                            'ValueType' => 'Percentage',
                            'Amount' => 6.862745098039227,
                            'RackAmount' => 6.862745098039227,
                            'DisplayableRackAmount' => 6.86,
                            'DisplayableAmount' => 6.86
                        ],
                    ],
                    'Fees' => [
                        [
                            'Code' => 'OBE_8',
                            'Description' => 'Enviromental Fee',
                            'ObeAction' => 'add',
                            'IsCommissionable' => true,
                            'Type' => 'Inclusive',
                            'MultiplierFee' => 1,
                            'CollectedBy' => 'vendor',
                            'Amount' => 10.0, //5 * 2
                            'RackAmount' => 20.0, //10 * 2
                            'ValueType' => 'Amount',
                            'DisplayableRackAmount' => 10.0,
                            'DisplayableAmount' => 10.0,
                        ],
                    ],
                ],
            ],
        ];
    }

    #[DataProvider('getTransformedBreakdownDataProvider')]
    public function testGetTransformedBreakdown(
        array $transformedRates,
        array $inputFees,
        array $expectedBreakdown
    ): void {
        $actualBreakdown = $this->taxAndFeeResolver->getTransformedBreakdown($transformedRates, $inputFees);
        $this->assertEquals($expectedBreakdown, $actualBreakdown);
    }

    /**
     * Data provider for testGetTransformedBreakdown.
     *
     * @return array
     */
    public static function getTransformedBreakdownDataProvider(): array
    {
        $inputFees = [
            'application fee',
            'banquet service fee',
            'city hotel fee',
            'crib fee',
            'early checkout fee',
            'express handling fee',
            'extra person charge',
            'local fee',
            'maintenance fee',
            'package fee',
            'resort fee',
            'rollaway fee',
            'room service fee',
            'service charge',
        ];

        return [
            'Test Case 1' => [
                'transformedRates' => [
                    [
                        'Code' => '',
                        'RateTimeUnit' => 'Day',
                        'UnitMultiplier' => '7',
                        'EffectiveDate' => '2025-07-16',
                        'ExpireDate' => '2025-07-23',
                        'AmountBeforeTax' => "125.00",
                        'AmountAfterTax' => "125.00",
                        'CurrencyCode' => 'USD',
                        'Taxes' => [
                            [
                                'Code' => 'OBE_5',
                                'Description' => 'Tax',
                                'ObeAction' => 'add',
                                'IsCommissionable' => false,
                                'Type' => 'Inclusive',
                                'MultiplierFee' => "7",
                                'CollectedBy' => 'vendor',
                                'Amount' => 12.5,
                                'RackAmount' => 12.5,
                                'DisplayableRackAmount' => 12.50,
                                'DisplayableAmount' => 12.50,
                                'ValueType' => 'Percentage',
                            ],
                        ],
                        'TotalAmountBeforeTax' => "875.00",
                        'TotalAmountAfterTax' => "875.00",
                        'TotalCurrencyCode' => 'USD',
                        'Fees' => [
                            [
                                'Code' => 'OBE_8',
                                'Description' => 'Fee',
                                'ObeAction' => 'add',
                                'IsCommissionable' => true,
                                'Type' => 'Inclusive',
                                'MultiplierFee' => 1,
                                'CollectedBy' => 'vendor',
                                'Amount' => 18.75,
                                'RackAmount' => 25.0,
                                'DisplayableAmount' => 18.75,
                                'DisplayableRackAmount' => 25.00,
                                'ValueType' => 'Percentage',
                            ],
                            [
                                // Assuming this is a service fee
                                'Code' => 'OBE_5',
                                'Amount' => 25.0,
                                'RackAmount' => 50.0,
                                'DisplayableAmount' => 25.0,
                                'DisplayableRackAmount' => 50.0,
                                'Description' => 'Service',
                                'IsCommissionable' => true,
                                'Type' => 'Inclusive',
                                'MultiplierFee' => 1,
                            ],
                        ],
                    ],

                ],
                'inputFees' => $inputFees,
                'expectedBreakdown' => [
                    'nightly' => [
                        [
                            [
                                'amount' => 112.50,
                                'rack_amount' => 0,
                                'title' => 'Base Rate',
                                'type' => 'base_rate',
                                'level' => 'rate',
                                'collected_by' => 'vendor',
                            ],
                            [
                                'type' => 'tax',
                                'amount' => 12.50,
                                'rack_amount' => 12.50,
                                'title' => 'Tax',
                                'is_commissionable' => false,
                                'collected_by' => 'vendor',
                                'level' => null,
                            ],
                        ],
                        [
                            [
                                'amount' => 112.50,
                                'rack_amount' => 0,
                                'title' => 'Base Rate',
                                'type' => 'base_rate',
                                'level' => 'rate',
                                'collected_by' => 'vendor',
                            ],
                            [
                                'type' => 'tax',
                                'amount' => 12.50,
                                'rack_amount' => 12.50,
                                'title' => 'Tax',
                                'is_commissionable' => false,
                                'collected_by' => 'vendor',
                                'level' => null,
                            ],
                        ],
                        [
                            [
                                'amount' => 112.50,
                                'rack_amount' => 0,
                                'title' => 'Base Rate',
                                'type' => 'base_rate',
                                'level' => 'rate',
                                'collected_by' => 'vendor',
                            ],
                            [
                                'type' => 'tax',
                                'amount' => 12.50,
                                'rack_amount' => 12.50,
                                'title' => 'Tax',
                                'is_commissionable' => false,
                                'collected_by' => 'vendor',
                                'level' => null,
                            ],
                        ],
                        [
                            [
                                'amount' => 112.50,
                                'rack_amount' => 0,
                                'title' => 'Base Rate',
                                'type' => 'base_rate',
                                'level' => 'rate',
                                'collected_by' => 'vendor',
                            ],
                            [
                                'type' => 'tax',
                                'amount' => 12.50,
                                'rack_amount' => 12.50,
                                'title' => 'Tax',
                                'is_commissionable' => false,
                                'collected_by' => 'vendor',
                                'level' => null,
                            ],
                        ],
                        [
                            [
                                'amount' => 112.50,
                                'rack_amount' => 0,
                                'title' => 'Base Rate',
                                'type' => 'base_rate',
                                'level' => 'rate',
                                'collected_by' => 'vendor',
                            ],
                            [
                                'type' => 'tax',
                                'amount' => 12.50,
                                'rack_amount' => 12.50,
                                'title' => 'Tax',
                                'is_commissionable' => false,
                                'collected_by' => 'vendor',
                                'level' => null,
                            ],
                        ],
                        [
                            [
                                'amount' => 112.50,
                                'rack_amount' => 0,
                                'title' => 'Base Rate',
                                'type' => 'base_rate',
                                'level' => 'rate',
                                'collected_by' => 'vendor',
                            ],
                            [
                                'type' => 'tax',
                                'amount' => 12.50,
                                'rack_amount' => 12.50,
                                'title' => 'Tax',
                                'is_commissionable' => false,
                                'collected_by' => 'vendor',
                                'level' => null,
                            ],
                        ],
                        [
                            [
                                'amount' => 112.50,
                                'rack_amount' => 0,
                                'title' => 'Base Rate',
                                'type' => 'base_rate',
                                'level' => 'rate',
                                'collected_by' => 'vendor',
                            ],
                            [
                                'type' => 'tax',
                                'amount' => 12.50,
                                'rack_amount' => 12.50,
                                'title' => 'Tax',
                                'is_commissionable' => false,
                                'collected_by' => 'vendor',
                                'level' => null,
                            ],
                        ],
                    ],
                    'stay' => [],
                    'fees' => [
                        [
                            'type' => 'fee',
                            'amount' => 18.75,
                            'rack_amount' => 25.0,
                            'title' => 'Fee',
                            'multiplier' => 1,
                            'is_commissionable' => true,
                            'collected_by' => 'vendor',
                            'level' => null,
                        ],
                        [
                            'type' => 'fee',
                            'amount' => 25.0,
                            'rack_amount' => 50.0,
                            'title' => 'Service',
                            'multiplier' => 1,
                            'is_commissionable' => true,
                            'level' => null,
                        ],
                    ],
                ],
            ],

            'Test Case 2' => [
                'transformedRates' => [
                    [
                        "Code" => "",
                        "RateTimeUnit" => "Day",
                        "UnitMultiplier" => "3",
                        "EffectiveDate" => "2025-07-16",
                        "ExpireDate" => "2025-07-19",
                        "AmountBeforeTax" => "125.00",
                        "AmountAfterTax" => "125.00",
                        "CurrencyCode" => "USD",
                        "Taxes" => [
                            [
                                "Code" => "OBE_5",
                                "Description" => "City Tax ",
                                "ObeAction" => "add",
                                "IsCommissionable" => false,
                                "Type" => "Inclusive",
                                "MultiplierFee" => "3",
                                "CollectedBy" => "vendor",
                                "Amount" => 6.25,
                                "RackAmount" => 6.25,
                                "DisplayableRackAmount" => 6.25,
                                "DisplayableAmount" => 6.25,
                                "ValueType" => "Percentage",
                            ]
                        ],
                        "TotalAmountBeforeTax" => "375.00",
                        "TotalAmountAfterTax" => "375.00",
                        "TotalCurrencyCode" => "USD",
                        "Fees" => [
                            [
                                "Code" => "OBE_8",
                                "Description" => "Resort Fee",
                                "ObeAction" => "add",
                                "IsCommissionable" => true,
                                "Type" => "Inclusive",
                                "MultiplierFee" => 1,
                                "CollectedBy" => "vendor",
                                "Amount" => 30.0,
                                "RackAmount" => 40.0,
                                "DisplayableAmount" => 30.0,
                                "DisplayableRackAmount" => 40.0,
                                "ValueType" => "Amount"
                            ],
                            [
                                "Code" => "OBE_9",
                                "Description" => "Service Fee",
                                "ObeAction" => "add",
                                "IsCommissionable" => true,
                                "Type" => "PropertyCollects",
                                "MultiplierFee" => 2,
                                "CollectedBy" => "direct",
                                "Amount" => 20.0,
                                "RackAmount" => 40.0,
                                "DisplayableAmount" => 20.0,
                                "DisplayableRackAmount" => 40.0,
                                "ValueType" => "Amount"
                            ]
                        ]
                    ]
                ],
                'inputFees' => $inputFees,
                'expectedBreakdown' => [
                    'nightly' => [
                        [
                            [
                                'amount' => 118.75,
                                'rack_amount' => 0,
                                'title' => 'Base Rate',
                                'type' => 'base_rate',
                                'level' => 'rate',
                                'collected_by' => 'vendor',
                            ],
                            [
                                'type' => 'tax',
                                'amount' => 6.25,
                                'rack_amount' => 6.25,
                                'title' => 'City Tax ',
                                'is_commissionable' => false,
                                'collected_by' => 'vendor',
                                'level' => null,
                            ],
                        ],
                        [
                            [
                                'amount' => 118.75,
                                'rack_amount' => 0,
                                'title' => 'Base Rate',
                                'type' => 'base_rate',
                                'level' => 'rate',
                                'collected_by' => 'vendor',
                            ],
                            [
                                'type' => 'tax',
                                'amount' => 6.25,
                                'rack_amount' => 6.25,
                                'title' => 'City Tax ',
                                'is_commissionable' => false,
                                'collected_by' => 'vendor',
                                'level' => null,
                            ],
                        ],
                        [
                            [
                                'amount' => 118.75,
                                'rack_amount' => 0,
                                'title' => 'Base Rate',
                                'type' => 'base_rate',
                                'level' => 'rate',
                                'collected_by' => 'vendor',
                            ],
                            [
                                'type' => 'tax',
                                'amount' => 6.25,
                                'rack_amount' => 6.25,
                                'title' => 'City Tax ',
                                'is_commissionable' => false,
                                'collected_by' => 'vendor',
                                'level' => null,
                            ],
                        ],
                    ],
                    'stay' => [],
                    'fees' => [
                        [
                            'type' => 'fee',
                            'amount' => 30.0,
                            'rack_amount' => 40.0,
                            'title' => 'Resort Fee',
                            'multiplier' => 1,
                            'is_commissionable' => true,
                            'collected_by' => 'vendor',
                            'level' => null,
                        ],
                        [
                            'type' => 'fee',
                            'amount' => 40.0,
                            'rack_amount' => 40.0,
                            'title' => 'Service Fee',
                            'multiplier' => 2,
                            'is_commissionable' => true,
                            'collected_by' => 'direct',
                            'level' => null,
                        ],
                    ],
                ],
            ],

            'Test Case 3' => [
                'transformedRates' => [
                    [
                        'Code' => '',
                        'RateTimeUnit' => 'Day',
                        'UnitMultiplier' => '3',
                        'EffectiveDate' => '2025-07-16',
                        'ExpireDate' => '2025-07-19',
                        'AmountBeforeTax' => '125.00',
                        'AmountAfterTax' => '125.00',
                        'CurrencyCode' => 'USD',
                        'Taxes' => [
                            [
                                'Code' => 'OBE_5',
                                'Description' => 'Tourism Tax',
                                'ObeAction' => 'add',
                                'IsCommissionable' => false,
                                'Type' => 'Inclusive',
                                'MultiplierFee' => '3',
                                'CollectedBy' => 'vendor',
                                'Amount' => 5.0,
                                'RackAmount' => 5.0,
                                'DisplayableRackAmount' => 5.0,
                                'DisplayableAmount' => 5.0,
                                'ValueType' => 'Amount'
                            ]
                        ],
                        'TotalAmountBeforeTax' => '375.00',
                        'TotalAmountAfterTax' => '375.00',
                        'TotalCurrencyCode' => 'USD',
                        'Fees' => [
                            [
                                'Code' => 'OBE_8',
                                'Description' => 'Cleaning Fee',
                                'ObeAction' => 'add',
                                'IsCommissionable' => true,
                                'Type' => 'Inclusive',
                                'MultiplierFee' => 1,
                                'CollectedBy' => 'vendor',
                                'Amount' => 45.0,
                                'RackAmount' => 90.0,
                                'DisplayableAmount' => 45.0,
                                'DisplayableRackAmount' => 90.0,
                                'ValueType' => 'Amount'
                            ]
                        ]
                    ]
                ],
                'inputFees' => $inputFees,
                'expectedBreakdown' => [
                    'nightly' => [
                        [
                            [
                                'amount' => 120.00,
                                'rack_amount' => 0,
                                'title' => 'Base Rate',
                                'type' => 'base_rate',
                                'level' => 'rate',
                                'collected_by' => 'vendor',
                            ],
                            [
                                'type' => 'tax',
                                'amount' => 5.0,
                                'rack_amount' => 5.0,
                                'title' => 'Tourism Tax',
                                'is_commissionable' => false,
                                'collected_by' => 'vendor',
                                'level' => null,
                            ],
                        ],
                        [
                            [
                                'amount' => 120.00,
                                'rack_amount' => 0,
                                'title' => 'Base Rate',
                                'type' => 'base_rate',
                                'level' => 'rate',
                                'collected_by' => 'vendor',
                            ],
                            [
                                'type' => 'tax',
                                'amount' => 5.0,
                                'rack_amount' => 5.0,
                                'title' => 'Tourism Tax',
                                'is_commissionable' => false,
                                'collected_by' => 'vendor',
                                'level' => null,
                            ],
                        ],
                        [
                            [
                                'amount' => 120.00,
                                'rack_amount' => 0,
                                'title' => 'Base Rate',
                                'type' => 'base_rate',
                                'level' => 'rate',
                                'collected_by' => 'vendor',
                            ],
                            [
                                'type' => 'tax',
                                'amount' => 5.0,
                                'rack_amount' => 5.0,
                                'title' => 'Tourism Tax',
                                'is_commissionable' => false,
                                'collected_by' => 'vendor',
                                'level' => null,
                            ],
                        ],
                    ],
                    'stay' => [],
                    'fees' => [
                        [
                            'type' => 'fee',
                            'amount' => 45.0,
                            'rack_amount' => 90.0,
                            'title' => 'Cleaning Fee',
                            'multiplier' => 1,
                            'is_commissionable' => true,
                            'collected_by' => 'vendor',
                            'level' => null,
                        ],
                    ],
                ],
            ],

            'Test Case 4' => [
                'transformedRates' => [
                    [
                        'Code' => '',
                        'RateTimeUnit' => 'Day',
                        'UnitMultiplier' => '3',
                        'EffectiveDate' => '2025-07-16',
                        'ExpireDate' => '2025-07-19',
                        'AmountBeforeTax' => '125.00',
                        'AmountAfterTax' => '125.00',
                        'CurrencyCode' => 'USD',
                        'Taxes' => [
                            [
                                'Code' => 'OBE_5',
                                'Description' => 'Occupancy Tax',
                                'ObeAction' => 'add',
                                'IsCommissionable' => false,
                                'Type' => 'Inclusive',
                                'MultiplierFee' => '3',
                                'CollectedBy' => 'vendor',
                                'Amount' => 5.0,
                                'RackAmount' => 5.0,
                                'DisplayableRackAmount' => 5.0,
                                'DisplayableAmount' => 5.0,
                                'ValueType' => 'Percentage'
                            ]
                        ],
                        'TotalAmountBeforeTax' => '375.00',
                        'TotalAmountAfterTax' => '375.00',
                        'TotalCurrencyCode' => 'USD',
                        'Fees' => [
                            [
                                'Code' => 'OBE_8',
                                'Description' => 'Enviromental Fee',
                                'ObeAction' => 'add',
                                'IsCommissionable' => true,
                                'Type' => 'Inclusive',
                                'MultiplierFee' => 1,
                                'CollectedBy' => 'vendor',
                                'Amount' => 10.0,
                                'RackAmount' => 20.0,
                                'DisplayableAmount' => 10.0,
                                'DisplayableRackAmount' => 20.0,
                                'ValueType' => 'Amount'
                            ]
                        ]
                    ]
                ],
                'inputFees' => $inputFees,
                'expectedBreakdown' => [
                    'nightly' => [
                        [
                            [
                                'amount' => 120.00,
                                'rack_amount' => 0,
                                'title' => 'Base Rate',
                                'type' => 'base_rate',
                                'level' => 'rate',
                                'collected_by' => 'vendor',
                            ],
                            [
                                'type' => 'tax',
                                'amount' => 5.0,
                                'rack_amount' => 5.0,
                                'title' => 'Occupancy Tax',
                                'is_commissionable' => false,
                                'collected_by' => 'vendor',
                                'level' => null,
                            ],
                        ],
                        [
                            [
                                'amount' => 120.00,
                                'rack_amount' => 0,
                                'title' => 'Base Rate',
                                'type' => 'base_rate',
                                'level' => 'rate',
                                'collected_by' => 'vendor',
                            ],
                            [
                                'type' => 'tax',
                                'amount' => 5.0,
                                'rack_amount' => 5.0,
                                'title' => 'Occupancy Tax',
                                'is_commissionable' => false,
                                'collected_by' => 'vendor',
                                'level' => null,
                            ],
                        ],
                        [
                            [
                                'amount' => 120.00,
                                'rack_amount' => 0,
                                'title' => 'Base Rate',
                                'type' => 'base_rate',
                                'level' => 'rate',
                                'collected_by' => 'vendor',
                            ],
                            [
                                'type' => 'tax',
                                'amount' => 5.0,
                                'rack_amount' => 5.0,
                                'title' => 'Occupancy Tax',
                                'is_commissionable' => false,
                                'collected_by' => 'vendor',
                                'level' => null,
                            ],
                        ],
                    ],
                    'stay' => [],
                    'fees' => [
                        [
                            'type' => 'fee',
                            'amount' => 10.0,
                            'rack_amount' => 20.0,
                            'title' => 'Enviromental Fee',
                            'multiplier' => 1,
                            'is_commissionable' => true,
                            'collected_by' => 'vendor',
                            'level' => null,
                        ],
                    ],
                ],
            ],
        ];
    }
}
