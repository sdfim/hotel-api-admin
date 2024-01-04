<?php

namespace Tests\Unit\Hotel\Pricing;

use App\Repositories\PropertyWeightingRepository;
use Illuminate\Support\Collection;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\API\PropertyWeighting\EnrichmentWeight;

class EnrichmentWeightPricingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @return void
     */
    public function test_enrichment_pricing_assert_equals_true(): void
    {
        $mockClientResponse = $this->createMockClientResponse();
        $mockWeights = $this->createMockWeights();

        $mockPropertyWeightingRepository = \Mockery::mock('overload:' . PropertyWeightingRepository::class);
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

        /*
        Вот что делает каждая строка:
        Mockery::mock('overload:' . PropertyWeightingRepository::class); - создает мок-объект для класса PropertyWeightingRepository.
            Ключевое слово overload указывает, что все новые экземпляры этого класса в тесте будут заменены на этот мок-объект.
        $mockPropertyWeightingRepository->shouldReceive('getWeights')->andReturn($mockWeights); - настраивает мок-объект так,
            чтобы при вызове метода getWeights он возвращал значение, хранящееся в переменной $mockWeights.
        $mockPropertyWeightingRepository->shouldReceive('getWeightsNot')->andReturn($mockWeights); - аналогично предыдущей строке,
            но для метода getWeightsNot.
        */
        $mockPropertyWeightingRepository = \Mockery::mock('overload:' . PropertyWeightingRepository::class);
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
            (object) ['property' => 1, 'weight' => 1],
            (object) ['property' => 2, 'weight' => 2],
        ]);
    }

}
