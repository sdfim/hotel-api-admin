<?php

use Illuminate\Foundation\Testing\WithFaker;

uses(WithFaker::class);

test('hotel search method response 200', function () {
    $hotelSearchData = hotelSearchData();

    $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

    $hotelSearchResponse
        ->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);
});

test('hotel search without type method response 400', function () {
    $hotelSearchData = hotelSearchWithoutTypeData();

    $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

    $hotelSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => 'Invalid type',
        ]);
});

test('hotel search with incorrect type method response 400', function () {
    $hotelSearchData = hotelSearchWithIncorrectTypeData();

    $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

    $hotelSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => 'Invalid type',
        ]);
});

test('hotel search with incorrect destination method response 400', function () {
    $hotelSearchData = hotelSearchWithIncorrectDestinationData();

    $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

    $hotelSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
        ]);
});

test('hotel search with incorrect rating method response 400', function () {
    $hotelSearchData = hotelSearchWithIncorrectRatingData();

    $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

    $hotelSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'rating' => ['The rating must be a number.'],
            ],
        ]);
});

test('hotel search by coordinates method response 200', function () {
    $hotelSearchData = hotelSearchByCoordinatesData();

    $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

    $hotelSearchResponse
        ->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);
});

test('hotel search by coordinates without type method response 400', function () {
    $hotelSearchData = hotelSearchByCoordinatesWithoutTypeData();

    $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

    $hotelSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => 'Invalid type',
        ]);
});

test('hotel search by coordinates with incorrect type method response 400', function () {
    $hotelSearchData = hotelSearchByCoordinatesWithIncorrectTypeData();

    $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

    $hotelSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => 'Invalid type',
        ]);
});

test('hotel search by coordinates without latitude method response 400', function () {
    $hotelSearchData = hotelSearchByCoordinatesWithoutLatitudeData();

    $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

    $hotelSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'latitude' => ['The latitude field is required when destination is not present.'],
            ],
        ]);
});

test('hotel search by coordinates with incorrect latitude method response 400', function () {
    $hotelSearchData = hotelSearchByCoordinatesWithIncorrectLatitudeData();

    $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

    $hotelSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'latitude' => ['The latitude must be at least -90.'],
            ],
        ]);
});

test('hotel search by coordinates without longitude method response 400', function () {
    $hotelSearchData = hotelSearchByCoordinatesWithoutLongitudeData();

    $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

    $hotelSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'longitude' => ['The longitude field is required when destination is not present.'],
            ],
        ]);
});

test('hotel search by coordinates with incorrect longitude method response 400', function () {
    $hotelSearchData = hotelSearchByCoordinatesWithIncorrectLongitudeData();

    $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

    $hotelSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'longitude' => ['The longitude must be at least -180.'],
            ],
        ]);
});

test('hotel search by coordinates without radius method response 400', function () {
    $hotelSearchData = hotelSearchByCoordinatesWithoutRadiusData();

    $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

    $hotelSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'radius' => ['The radius field is required when destination is not present.'],
            ],
        ]);
});

test('hotel search by coordinates with incorrect radius method response 400', function () {
    $hotelSearchData = hotelSearchByCoordinatesWithIncorrectRadiusData();

    $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

    $hotelSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'radius' => ['The radius must be between 1 and 100.'],
            ],
        ]);
});

test('hotel search by coordinates without rating method response 400', function () {
    $hotelSearchData = hotelSearchByCoordinatesWithoutRatingData();

    $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

    $hotelSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'radius' => ['The radius must be between 1 and 100.'],
            ],
        ]);
});

test('hotel search by coordinates with incorrect rating method response 400', function () {
    $hotelSearchData = hotelSearchByCoordinatesWithIncorrectRatingData();

    $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

    $hotelSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'rating' => ['The rating must be between 1 and 5.5.'],
            ],
        ]);
});

test('hotel search by coordinates without page method response 200', function () {
    $hotelSearchData = hotelSearchByCoordinatesWithoutPageData();

    $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

    $hotelSearchResponse
        ->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);
});

test('hotel search by coordinates with incorrect page method response 400', function () {
    $hotelSearchData = hotelSearchByCoordinatesWithIncorrectPageData();

    $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

    $hotelSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'page' => ['The page must be between 1 and 1000.'],
            ],
        ]);
});

test('hotel search by coordinates without results per page method response 200', function () {
    $hotelSearchData = hotelSearchByCoordinatesWithoutResultsPerPageData();

    $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

    $hotelSearchResponse
        ->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);
});

test('hotel search by coordinates with incorrect results per page method response 400', function () {
    $hotelSearchData = hotelSearchByCoordinatesWithIncorrectResultsPerPageData();

    $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

    $hotelSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'results_per_page' => ['The results per page must be between 1 and 1000.'],
            ],
        ]);
});

function hotelSearchData(): array
{
    return [
        'type' => 'hotel',
        'destination' => test()->faker->randomElement([961, 302, 93, 960, 1102]),
        'rating' => test()->faker->randomFloat(1, 1, 5.5),
        'page' => 1,
        'results_per_page' => 250,
    ];
}

function hotelSearchWithoutTypeData(): array
{
    return [
        'type' => '',
        'destination' => test()->faker->randomElement([961, 302, 93, 960, 1102]),
        'rating' => test()->faker->randomFloat(1, 1, 5.5),
        'page' => 1,
        'results_per_page' => 250,
    ];
}

function hotelSearchWithIncorrectTypeData(): array
{
    return [
        'type' => 'wrong_type',
        'destination' => test()->faker->randomElement([961, 302, 93, 960, 1102]),
        'rating' => test()->faker->randomFloat(1, 1, 5.5),
        'page' => 1,
        'results_per_page' => 250,
    ];
}

function hotelSearchWithIncorrectDestinationData(): array
{
    return [
        'type' => 'hotel',
        'destination' => '',
        'rating' => test()->faker->randomFloat(1, 1, 5.5),
        'page' => 1,
        'results_per_page' => 250,
    ];
}

function hotelSearchWithIncorrectRatingData(): array
{
    return [
        'type' => 'hotel',
        'destination' => test()->faker->randomElement([961, 302, 93, 960, 1102]),
        'rating' => '',
        'page' => 1,
        'results_per_page' => 250,
    ];
}

function hotelSearchByCoordinatesData(): array
{
    return [
        'type' => 'hotel',
        'latitude' => test()->faker->randomFloat(2, -90, 90),
        'longitude' => test()->faker->randomFloat(2, -180, 180),
        'radius' => rand(10, 50),
        'rating' => test()->faker->randomFloat(1, 1, 5.5),
        'page' => 1,
        'results_per_page' => 20,
    ];
}

function hotelSearchByCoordinatesWithoutTypeData(): array
{
    return [
        'latitude' => test()->faker->randomFloat(2, -90, 90),
        'longitude' => test()->faker->randomFloat(2, -180, 180),
        'radius' => rand(10, 50),
        'rating' => test()->faker->randomFloat(1, 1, 5.5),
        'page' => 1,
        'results_per_page' => 20,
    ];
}

function hotelSearchByCoordinatesWithIncorrectTypeData(): array
{
    return [
        'type' => 'wrong_type',
        'latitude' => test()->faker->randomFloat(2, -90, 90),
        'longitude' => test()->faker->randomFloat(2, -180, 180),
        'radius' => rand(10, 50),
        'rating' => test()->faker->randomFloat(1, 1, 5.5),
        'page' => 1,
        'results_per_page' => 20,
    ];
}

function hotelSearchByCoordinatesWithoutLatitudeData(): array
{
    return [
        'type' => 'hotel',
        'longitude' => test()->faker->randomFloat(2, -180, 180),
        'radius' => rand(10, 50),
        'rating' => test()->faker->randomFloat(1, 1, 5.5),
        'page' => 1,
        'results_per_page' => 20,
    ];
}

/**
 * Latitude:
 *
 * Valid range: -90° to +90°
 * Northern Hemisphere: 0° to +90°
 * Southern Hemisphere: 0° to -90°
 */
function hotelSearchByCoordinatesWithIncorrectLatitudeData(): array
{
    return [
        'type' => 'hotel',
        'latitude' => test()->faker->randomFloat(2, -180, -91),
        'longitude' => test()->faker->randomFloat(2, -180, 180),
        'radius' => rand(10, 50),
        'rating' => test()->faker->randomFloat(1, 1, 5.5),
        'page' => 1,
        'results_per_page' => 20,
    ];
}

function hotelSearchByCoordinatesWithoutLongitudeData(): array
{
    return [
        'type' => 'hotel',
        'latitude' => test()->faker->randomFloat(2, -90, 90),
        'radius' => rand(10, 50),
        'rating' => test()->faker->randomFloat(1, 1, 5.5),
        'page' => 1,
        'results_per_page' => 20,
    ];
}

/**
 * Longitude:
 *
 * Valid range: -180° to +180°
 * Eastern Hemisphere: 0° to +180°
 * Western Hemisphere: 0° to -180°
 */
function hotelSearchByCoordinatesWithIncorrectLongitudeData(): array
{
    return [
        'type' => 'hotel',
        'latitude' => test()->faker->randomFloat(2, -90, 90),
        'longitude' => test()->faker->randomFloat(2, -360, -180),
        'radius' => rand(10, 50),
        'rating' => test()->faker->randomFloat(1, 1, 5.5),
        'page' => 1,
        'results_per_page' => 20,
    ];
}

function hotelSearchByCoordinatesWithoutRadiusData(): array
{
    return [
        'type' => 'hotel',
        'latitude' => test()->faker->randomFloat(2, -90, 90),
        'longitude' => test()->faker->randomFloat(2, -180, 180),
        'rating' => test()->faker->randomFloat(1, 1, 5.5),
        'page' => 1,
        'results_per_page' => 20,
    ];
}

function hotelSearchByCoordinatesWithIncorrectRadiusData(): array
{
    return [
        'type' => 'hotel',
        'latitude' => test()->faker->randomFloat(2, -90, 90),
        'longitude' => test()->faker->randomFloat(2, -180, 180),
        'radius' => -1,
        'rating' => test()->faker->randomFloat(1, 1, 5.5),
        'page' => 1,
        'results_per_page' => 20,
    ];
}

function hotelSearchByCoordinatesWithoutRatingData(): array
{
    return [
        'type' => 'hotel',
        'latitude' => test()->faker->randomFloat(2, -90, 90),
        'longitude' => test()->faker->randomFloat(2, -180, 180),
        'radius' => -1,
        'page' => 1,
        'results_per_page' => 20,
    ];
}

function hotelSearchByCoordinatesWithIncorrectRatingData(): array
{
    return [
        'type' => 'hotel',
        'latitude' => test()->faker->randomFloat(2, -90, 90),
        'longitude' => test()->faker->randomFloat(2, -180, 180),
        'radius' => rand(10, 50),
        'rating' => -1,
        'page' => 1,
        'results_per_page' => 20,
    ];
}

function hotelSearchByCoordinatesWithoutPageData(): array
{
    return [
        'type' => 'hotel',
        'latitude' => test()->faker->randomFloat(2, -90, 90),
        'longitude' => test()->faker->randomFloat(2, -180, 180),
        'radius' => rand(10, 50),
        'rating' => test()->faker->randomFloat(1, 1, 5.5),
        'results_per_page' => 20,
    ];
}

function hotelSearchByCoordinatesWithIncorrectPageData(): array
{
    return [
        'type' => 'hotel',
        'latitude' => test()->faker->randomFloat(2, -90, 90),
        'longitude' => test()->faker->randomFloat(2, -180, 180),
        'radius' => rand(10, 50),
        'rating' => test()->faker->randomFloat(1, 1, 5.5),
        'page' => -1,
        'results_per_page' => 20,
    ];
}

function hotelSearchByCoordinatesWithoutResultsPerPageData(): array
{
    return [
        'type' => 'hotel',
        'latitude' => test()->faker->randomFloat(2, -90, 90),
        'longitude' => test()->faker->randomFloat(2, -180, 180),
        'radius' => rand(10, 50),
        'rating' => test()->faker->randomFloat(1, 1, 5.5),
        'page' => 1,
    ];
}

function hotelSearchByCoordinatesWithIncorrectResultsPerPageData(): array
{
    return [
        'type' => 'hotel',
        'latitude' => test()->faker->randomFloat(2, -90, 90),
        'longitude' => test()->faker->randomFloat(2, -180, 180),
        'radius' => rand(10, 50),
        'rating' => test()->faker->randomFloat(1, 1, 5.5),
        'page' => 1,
        'results_per_page' => -1,
    ];
}