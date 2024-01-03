<?php

namespace Tests\Unit\Hotel\Content;

use App\Repositories\PropertyWeightingRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Modules\API\PropertyWeighting\EnrichmentWeight;
use Tests\TestCase;

class EnrichmentWeightContentSearchTest  extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @return void
     */
    public function test_enrichment_content_assert_equals_true(): void
    {
        $mockClientResponse = $this->createMockClientResponse();
        $mockWeights = $this->createMockWeights();

        $mockPropertyWeightingRepository = \Mockery::mock('overload:' . PropertyWeightingRepository::class);
        $mockPropertyWeightingRepository->shouldReceive('getWeights')->andReturn($mockWeights);

        $enrichmentWeight = new EnrichmentWeight();
        $result = $enrichmentWeight->enrichmentContent($mockClientResponse, 'type');

        $expectedResult = [
            'supplier1' => [
                ['giata_hotel_code' => 1, 'weight' => 1],
                ['giata_hotel_code' => 2, 'weight' => 2],
            ],
            'supplier2' => [
                ['giata_hotel_code' => 3, 'weight' => 0],
                ['giata_hotel_code' => 4, 'weight' => 0],
            ],
        ];

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @test
     * @return void
     */
    public function test_enrichment_content_assert_equals_false(): void
    {
        $mockClientResponse = $this->createMockClientResponse();
        $mockWeights = $this->createMockWeights();

        $mockPropertyWeightingRepository = \Mockery::mock('overload:' . PropertyWeightingRepository::class);
        $mockPropertyWeightingRepository->shouldReceive('getWeights')->andReturn($mockWeights);

        $enrichmentWeight = new EnrichmentWeight();
        $result = $enrichmentWeight->enrichmentContent($mockClientResponse, 'type');

        $expectedResult = [
            'supplier1' => [
                ['giata_hotel_code' => 1, 'weight' => 0],
                ['giata_hotel_code' => 2, 'weight' => 0],
            ],
            'supplier2' => [
                ['giata_hotel_code' => 3, 'weight' => 1],
                ['giata_hotel_code' => 4, 'weight' => 2],
            ],
        ];

        $this->assertNotEquals($expectedResult, $result);
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

    /**
     * @return Collection
     */
    protected function createMockWeights(): Collection
    {
        return collect([
            (object) ['property' => 1, 'weight' => 1],
            (object) ['property' => 2, 'weight' => 2],
        ]);
    }
}
