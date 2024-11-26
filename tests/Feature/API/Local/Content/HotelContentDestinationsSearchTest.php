<?php

namespace Tests\Feature\API\Local\Content;

use Tests\Feature\API\ApiTestCase;
use PHPUnit\Framework\Attributes\Test;

class HotelContentDestinationsSearchTest extends ApiTestCase
{
    #[Test]
    public function test_hotel_destination_method_response_200(): void
    {
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
    }

    #[Test]
    public function test_hotel_destination_with_empty_city_method_response_400(): void
    {
        $hotelDestinationSearchResponse = $this->withHeaders($this->headers)->get('/api/content/destinations?city=');

        $hotelDestinationSearchResponse
            ->assertStatus(400)
            ->assertJson([
                'error' => 'Invalid city',
            ]);
    }

    #[Test]
    public function test_hotel_destination_without_city_method_response_400(): void
    {
        $hotelDestinationSearchResponse = $this->withHeaders($this->headers)->get('/api/content/destinations');

        $hotelDestinationSearchResponse
            ->assertStatus(400)
            ->assertJson([
                'error' => 'Invalid city',
            ]);
    }
}
