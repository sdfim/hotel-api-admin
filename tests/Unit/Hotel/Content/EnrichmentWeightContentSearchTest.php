<?php

namespace Tests\Unit\Hotel\Content;

use App\Repositories\PropertyWeightingRepository;
use Illuminate\Support\Collection;
use Mockery;
use Modules\API\PropertyWeighting\EnrichmentWeight;
use Tests\TestCase;

class EnrichmentWeightContentSearchTest extends TestCase
{
    /**
     * @test
     */
    public function test_enrichment_content_assert_equals_true(): void
    {
        $mockClientResponse = $this->createMockClientResponse();
        $mockWeights = $this->createMockWeights();

        $mockPropertyWeightingRepository = Mockery::mock('overload:'.PropertyWeightingRepository::class);
        $mockPropertyWeightingRepository->shouldReceive('getWeights')->andReturn($mockWeights);

        $enrichmentWeight = new EnrichmentWeight();
        $result = $enrichmentWeight->enrichmentContent($mockClientResponse, 'type');

        $expectedResult = $this->getExpectedResult();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @test
     */
    public function test_enrichment_content_assert_equals_false(): void
    {
        $mockClientResponse = $this->createMockClientResponse();
        $mockWeights = $this->createMockWeights();

        $mockPropertyWeightingRepository = Mockery::mock('overload:'.PropertyWeightingRepository::class);
        $mockPropertyWeightingRepository->shouldReceive('getWeights')->andReturn($mockWeights);

        $enrichmentWeight = new EnrichmentWeight();
        $result = $enrichmentWeight->enrichmentContent($mockClientResponse, 'type');

        $expectedResult = $this->getExpectedResult(false);

        $this->assertNotEquals($expectedResult, $result);
    }

    private function getExpectedResult(bool $type = true): array
    {
        if ($type) {
            return [
                'supplier1' => [
                    ['giata_hotel_code' => 1, 'weight' => 1],
                    ['giata_hotel_code' => 2, 'weight' => 2],
                ],
                'supplier2' => [
                    ['giata_hotel_code' => 3, 'weight' => 0],
                    ['giata_hotel_code' => 4, 'weight' => 0],
                ],
            ];
        } else {
            return [
                'supplier1' => [
                    ['giata_hotel_code' => 1, 'weight' => 0],
                    ['giata_hotel_code' => 2, 'weight' => 0],
                ],
                'supplier2' => [
                    ['giata_hotel_code' => 3, 'weight' => 1],
                    ['giata_hotel_code' => 4, 'weight' => 2],
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
                ['giata_hotel_code' => 1],
                ['giata_hotel_code' => 2],
            ],
            'supplier2' => [
                ['giata_hotel_code' => 3],
                ['giata_hotel_code' => 4],
            ],
        ];
    }

    protected function createMockWeights(): Collection
    {
        return collect([
            (object) ['property' => 1, 'weight' => 1],
            (object) ['property' => 2, 'weight' => 2],
        ]);
    }
}
