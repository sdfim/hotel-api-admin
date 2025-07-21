<?php

use App\Models\PropertyWeighting;
use App\Repositories\PropertyWeightingRepository;
use Illuminate\Database\Eloquent\Collection;
use Modules\API\PropertyWeighting\EnrichmentWeight;

uses(Tests\TestCase::class);

beforeEach(function () {
    $this->mockClientResponse = createMockClientResponseForContentSearch();
    $this->mockWeights = createMockWeightsForContentSearch();
});

test('enrichment content assert equals true', function () {
    $mockPropertyWeightingRepository = Mockery::mock(PropertyWeightingRepository::class);
    $mockPropertyWeightingRepository->shouldReceive('getWeights')->once()->andReturn($this->mockWeights);

    $enrichmentWeight = new EnrichmentWeight($mockPropertyWeightingRepository);
    $result = $enrichmentWeight->enrichmentContent($this->mockClientResponse, 'type');

    $expectedResult = getExpectedResultForContentSearch(true);

    expect($result)->toEqual($expectedResult);
});

test('enrichment content assert equals false', function () {
    $mockPropertyWeightingRepository = Mockery::mock(PropertyWeightingRepository::class);
    $mockPropertyWeightingRepository->shouldReceive('getWeights')->andReturn($this->mockWeights);

    $enrichmentWeight = new EnrichmentWeight($mockPropertyWeightingRepository);
    $result = $enrichmentWeight->enrichmentContent($this->mockClientResponse, 'type');

    $expectedResult = getExpectedResultForContentSearch(false);

    expect($result)->not->toEqual($expectedResult);
});

function getExpectedResultForContentSearch(bool $type = true): array
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

function createMockClientResponseForContentSearch(): array
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

function createMockWeightsForContentSearch(): Collection
{
    return new Collection([
        new PropertyWeighting(['property' => 1, 'weight' => 1]),
        new PropertyWeighting(['property' => 2, 'weight' => 2]),
    ]);
}
