<?php

namespace Tests\Feature\API\Content;

use Tests\Feature\API\ApiTestCase;

class HotelContentDestinationsSearchTest extends ApiTestCase
{
    /**
     * @test
     * @return void
     */
    public function test_hotel_destination_method_response_200()
    {
        $hotelDestinationSearchResponse = $this->withHeaders($this->headers)->get('/api/content/destinations?city=London');

        $hotelDestinationSearchResponse
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'full_name',
                        'city_id'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_destination_with_empty_city_method_response_400()
    {
        $hotelDestinationSearchResponse = $this->withHeaders($this->headers)->get('/api/content/destinations?city=');

        $hotelDestinationSearchResponse
            ->assertStatus(400)
            ->assertJson([
                'error' => 'Invalid city',
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_destination_without_city_method_response_400()
    {
        $hotelDestinationSearchResponse = $this->withHeaders($this->headers)->get('/api/content/destinations');

        $hotelDestinationSearchResponse
            ->assertStatus(400)
            ->assertJson([
                'error' => 'Invalid city',
            ]);
    }
}
