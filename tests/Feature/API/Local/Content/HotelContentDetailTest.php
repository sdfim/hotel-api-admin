<?php

use Illuminate\Foundation\Testing\WithFaker;

uses(WithFaker::class);

test('hotel detail method response true', function () {
    $hotelSearchData = hotelSearchData();

    $hotelSearchResponse = $this->withHeaders($this->headers)->postJson('/api/content/search', $hotelSearchData);

    $hotelInfo = $hotelSearchResponse['data']['results'];

    $hotelInfo = $hotelInfo['Expedia'][0];

    $hotelId = $hotelInfo['giata_hotel_code'];

    $hotelDetailResponse = $this->withHeaders($this->headers)->get("/api/content/detail?property_id=$hotelId&type=hotel");

    $hotelDetailResponse
        ->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);
});

test('hotel detail non existent property id method response 400', function () {
    $hotelDetailResponse = $this->withHeaders($this->headers)->get('/api/content/detail?property_id=99999999999999&type=hotel');

    $hotelDetailResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
        ]);
});

test('hotel detail with correct property id and missed type method response 400', function () {
    $hotelDetailResponse = $this->withHeaders($this->headers)->get('/api/content/detail?property_id=98736411');

    $hotelDetailResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => 'Invalid type',
        ]);
});

test('hotel detail with type and missed property id parameter method response 400', function () {
    $hotelDetailResponse = $this->withHeaders($this->headers)->get('/api/content/detail?type=hotel');

    $hotelDetailResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'property_id' => [
                    'The property id field is required.',
                ],
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