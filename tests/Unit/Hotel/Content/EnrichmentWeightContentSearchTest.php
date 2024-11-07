<?php

namespace Tests\Unit\Hotel\Content;

use App\Models\PropertyWeighting;
use App\Repositories\PropertyWeightingRepository;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Modules\API\PropertyWeighting\EnrichmentWeight;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class EnrichmentWeightContentSearchTest extends TestCase
{
    protected $mockClientResponse;
    protected $mockWeights;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockClientResponse = $this->createMockClientResponse();
        $this->mockWeights = $this->createMockWeights();
    }

    #[Test]
    public function test_enrichment_content_assert_equals_true(): void
    {
        $mockPropertyWeightingRepository = Mockery::mock(PropertyWeightingRepository::class);
        $mockPropertyWeightingRepository->shouldReceive('getWeights')->once()->andReturn($this->mockWeights);

        $enrichmentWeight = new EnrichmentWeight($mockPropertyWeightingRepository);
        $result = $enrichmentWeight->enrichmentContent($this->mockClientResponse, 'type');

        $expectedResult = $this->getExpectedResult(true);

        $this->assertEquals($expectedResult, $result);
    }

    #[Test]
    public function test_enrichment_content_assert_equals_false(): void
    {
        $mockPropertyWeightingRepository = Mockery::mock(PropertyWeightingRepository::class);
        $mockPropertyWeightingRepository->shouldReceive('getWeights')->andReturn($this->mockWeights);

        $enrichmentWeight = new EnrichmentWeight($mockPropertyWeightingRepository);
        $result = $enrichmentWeight->enrichmentContent($this->mockClientResponse, 'type');

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
        return new Collection([
            new PropertyWeighting(['property' => 1, 'weight' => 1]),
            new PropertyWeighting(['property' => 2, 'weight' => 2]),
        ]);
    }
}
