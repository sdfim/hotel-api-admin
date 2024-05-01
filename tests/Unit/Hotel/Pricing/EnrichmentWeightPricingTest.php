<?php

namespace Tests\Unit\Hotel\Pricing;

use App\Repositories\PropertyWeightingRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Mockery;
use Modules\API\PropertyWeighting\EnrichmentWeight;
use Tests\TestCase;

class EnrichmentWeightPricingTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function test_enrichment_pricing_assert_equals_true(): void
    {
        $mockClientResponse = $this->createMockClientResponse();
        $mockWeights = $this->createMockWeights();

        $mockPropertyWeightingRepository = Mockery::mock('overload:' . PropertyWeightingRepository::class);
        $mockPropertyWeightingRepository->shouldReceive('getWeights')->andReturn($mockWeights);
        $mockPropertyWeightingRepository->shouldReceive('getWeightsNot')->andReturn($mockWeights);

        $enrichmentWeight = new EnrichmentWeight();
        $result = $enrichmentWeight->enrichmentPricing($mockClientResponse, 'type');

        $expectedResult = $this->getExpectedResult();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @test
     * @return void
     */
    public function test_enrichment_pricing_assert_equals_false(): void
    {
        $mockClientResponse = $this->createMockClientResponse();
        $mockWeights = $this->createMockWeights();

        $mockPropertyWeightingRepository = Mockery::mock('overload:' . PropertyWeightingRepository::class);
        $mockPropertyWeightingRepository->shouldReceive('getWeights')->andReturn($mockWeights);
        $mockPropertyWeightingRepository->shouldReceive('getWeightsNot')->andReturn($mockWeights);

        $enrichmentWeight = new EnrichmentWeight();
        $result = $enrichmentWeight->enrichmentPricing($mockClientResponse, 'type');

        $expectedResult = $this->getExpectedResult(false);

        $this->assertNotEquals($expectedResult, $result);
    }

    /**
     * @param bool $type
     * @return array
     */
    private function getExpectedResult(bool $type = true): array
    {
        if ($type) {
            return [
                'supplier1' => [
                    ['giata_hotel_id' => 1, 'weight' => 1],
                    ['giata_hotel_id' => 2, 'weight' => 2],
                ],
                'supplier2' => [
                    ['giata_hotel_id' => 3, 'weight' => 0],
                    ['giata_hotel_id' => 4, 'weight' => 0],
                ],
            ];
        } else {
            return [
                'supplier1' => [
                    ['giata_hotel_id' => 1, 'weight' => 0],
                    ['giata_hotel_id' => 2, 'weight' => 0],
                ],
                'supplier2' => [
                    ['giata_hotel_id' => 3, 'weight' => 1],
                    ['giata_hotel_id' => 4, 'weight' => 2],
                ],
            ];
        }
    }

    /**
     * @return array[]
     */
    protected function createMockClientResponse(): array
    {
        return [
            'supplier1' => [
                ['giata_hotel_id' => 1],
                ['giata_hotel_id' => 2],
            ],
            'supplier2' => [
                ['giata_hotel_id' => 3],
                ['giata_hotel_id' => 4],
            ],
        ];
    }

    /**
     * @return Collection
     */
    protected function createMockWeights(): Collection
    {
        return collect([
            (object)['property' => 1, 'weight' => 1],
            (object)['property' => 2, 'weight' => 2],
        ]);
    }

}
