<?php

namespace Tests\Feature\API\Content;

use Feature\API\ApiTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class HotelContentDetailTest extends ApiTestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @var array|string[]
     */
    private array $headers;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seederSupplier();
        $this->headers = $this->getHeader();
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_detail_method_response_true()
    {
        $jsonData = $this->hotelSearchRequest();
        $response_search = $this->withHeaders($this->headers)->postJson('/api/content/search', $jsonData);
        $hotel_info = $response_search['data']['results'];
        $hotel_info = $hotel_info['Expedia'][0];
        $hotel_id = $hotel_info['giata_hotel_code'];

        $response_detail = $this->withHeaders($this->headers)->get('/api/content/detail?property_id=' . $hotel_id . '&type=hotel');

        $response_detail
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_detail_false_property_id_method_response_400()
    {
        $response_detail = $this->withHeaders($this->headers)->get('/api/content/detail?property_id=99999999999999&type=hotel');

        $response_detail
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_detail_without_type_parameter_method_response_400()
    {
        $response_detail = $this->withHeaders($this->headers)->get('/api/content/detail?property_id=99999999999999');
        $response_detail
            ->assertStatus(400)
            ->assertJson([
                'message' => 'Invalid type',
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_detail_without_property_id_parameter_method_response_400()
    {
        $response_detail = $this->withHeaders($this->headers)->get('/api/content/detail?type=hotel');
        $response_detail
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * @return array
     */
    private function hotelSearchRequest(): array
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
