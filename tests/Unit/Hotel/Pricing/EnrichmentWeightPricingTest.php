<?php

use App\Models\PropertyWeighting;
use App\Repositories\PropertyWeightingRepository;
use Illuminate\Database\Eloquent\Collection;
use Modules\API\PropertyWeighting\EnrichmentWeight;

uses(Tests\TestCase::class);

beforeEach(function () {
    $this->mockClientResponse = createMockClientResponseForPricing();
    $this->mockWeights = createMockWeightsForPricing();
});

test('enrichment pricing assert equals true', function () {
    $mockClientResponse = createMockClientResponseForPricing();
    $mockWeights = createMockWeightsForPricing();

    $mockPropertyWeightingRepository = Mockery::mock(PropertyWeightingRepository::class);
    $mockPropertyWeightingRepository->shouldReceive('getWeights')->andReturn($mockWeights);
    $mockPropertyWeightingRepository->shouldReceive('getWeightsNot')->andReturn($mockWeights);

    $enrichmentWeight = new EnrichmentWeight($mockPropertyWeightingRepository);
    $result = $enrichmentWeight->enrichmentPricing($mockClientResponse, 'type');

    $expectedResult = getExpectedResultForPricing();

    expect($result)->toEqual($expectedResult);
});

test('enrichment pricing assert equals false', function () {
    $mockClientResponse = createMockClientResponseForPricing();
    $mockWeights = createMockWeightsForPricing();

    $mockPropertyWeightingRepository = Mockery::mock(PropertyWeightingRepository::class);
    $mockPropertyWeightingRepository->shouldReceive('getWeights')->andReturn($mockWeights);
    $mockPropertyWeightingRepository->shouldReceive('getWeightsNot')->andReturn($mockWeights);

    $enrichmentWeight = new EnrichmentWeight($mockPropertyWeightingRepository);
    $result = $enrichmentWeight->enrichmentPricing($mockClientResponse, 'type');

    $expectedResult = getExpectedResultForPricing(false);

    expect($result)->not->toEqual($expectedResult);
});

function getExpectedResultForPricing(bool $type = true): array
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

function createMockClientResponseForPricing(): array
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

function createMockWeightsForPricing(): Collection
{
    return new Collection([
        new PropertyWeighting(['property' => 1, 'weight' => 1]),
        new PropertyWeighting(['property' => 2, 'weight' => 2]),
    ]);
}
