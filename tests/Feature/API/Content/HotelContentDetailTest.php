<?php

namespace Tests\Feature\API\Content;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\API\ApiTestCase;

class HotelContentDetailTest extends ApiTestCase
{
    use WithFaker;

    /**
     * @test
     *
     * @return void
     */
    public function test_hotel_detail_method_response_true(): void
    {
        $hotelSearchData = $this->hotelSearchData();

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
    }

    /**
     * @test
     *
     * @return void
     */
    public function test_hotel_detail_non_existent_property_id_method_response_400(): void
    {
        $hotelDetailResponse = $this->withHeaders($this->headers)->get('/api/content/detail?property_id=99999999999999&type=hotel');

        $hotelDetailResponse
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function test_hotel_detail_with_correct_property_id_and_missed_type_method_response_400(): void
    {
        $hotelDetailResponse = $this->withHeaders($this->headers)->get('/api/content/detail?property_id=98736411');

        $hotelDetailResponse
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => 'Invalid type',
            ]);
    }

    /**
     * @test
     *
     * @return void
     */
    public function test_hotel_detail_with_type_and_missed_property_id_parameter_method_response_400(): void
    {
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
    }

    private function hotelSearchData(): array
    {
        return [
            'type' => 'hotel',
            'destination' => $this->faker->randomElement([961, 302, 93, 960, 1102]),
            'rating' => $this->faker->randomFloat(1, 1, 5.5),
            'page' => 1,
            'results_per_page' => 250,
        ];
    }
}
