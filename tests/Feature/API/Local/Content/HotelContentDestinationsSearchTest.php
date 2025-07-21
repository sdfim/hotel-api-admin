<?php

test('hotel destination method response 200', function () {
    $hotelDestinationSearchResponse = $this->withHeaders($this->headers)->get('/api/content/destinations?city=London');

    $hotelDestinationSearchResponse
        ->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'full_name',
                    'city_id',
                ],
            ],
        ]);
});

test('hotel destination with empty city method response 400', function () {
    $hotelDestinationSearchResponse = $this->withHeaders($this->headers)->get('/api/content/destinations?city=');

    $hotelDestinationSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'error' => 'Invalid city',
        ]);
});

test('hotel destination without city method response 400', function () {
    $hotelDestinationSearchResponse = $this->withHeaders($this->headers)->get('/api/content/destinations');

    $hotelDestinationSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'error' => 'Invalid city',
        ]);
});