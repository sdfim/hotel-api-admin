<?php

namespace Tests\Unit\Modules\API\PricingAPI\Resolvers\TaxAndFees;

use Carbon\Carbon;
use Modules\API\Suppliers\HBSI\Resolvers\HbsiTaxAndFeeResolver;
use PHPUnit\Framework\TestCase;

/**
 * Class TaxAndFeeResolverTest
 *
 * This class tests the TaxAndFeeResolver functionality.
 *
 * @todo Get data from the database instead of hardcoding it.
 */
class BaseTaxAndFeeResolverTest extends TestCase
{
    public HbsiTaxAndFeeResolver $taxAndFeeResolver;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->taxAndFeeResolver = new HbsiTaxAndFeeResolver;
    }

    /**
     * Helper method to create base transformed rates structure
     */
    public function createBaseTransformedRates(float $baseNetRate, int $nights = 1, float $baseRackRate = 0): array
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
            ],
        ];
    }

    /**
     * Helper method to execute applyRepoTaxFees and return result
     */
    public function executeApplyRepoTaxFees(array &$transformedRates, array $repoTaxFeesInput, int $numberOfPassengers = 2, int $nights = 7): void
    {
        $giataCode = 1002;
        $ratePlanCode = 'Loyalty';
        $unifiedRoomCode = '';
        $checkinInput = Carbon::parse('2025-08-10')->startOfDay()->toDateString();
        $checkoutInput = Carbon::parse('2025-08-10')->startOfDay()->addDays($nights)->toDateString();
        $occupancy = [
            [
                'adults' => 2,
            ],
        ];
        $repoTaxFees = $repoTaxFeesInput[1002];

        $this->taxAndFeeResolver->applyRepoTaxFees(
            $transformedRates,
            $numberOfPassengers,
            $checkinInput,
            $checkoutInput,
            $repoTaxFees,
            $occupancy
        );
    }

    /**
     * Helper method to assert tax and fee results
     */
    public function assertTaxAndFeeResults(array $transformedRates, array $expectedResult): void
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
}
