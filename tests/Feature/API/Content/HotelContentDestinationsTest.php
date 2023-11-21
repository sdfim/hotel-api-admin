<?php

namespace Tests\Feature\API\Content;

use Feature\API\ApiTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HotelContentDestinationsTest extends ApiTestCase
{
    use RefreshDatabase;

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
    public function test_hotel_destination_method_response_true()
    {
        $response_detail = $this->withHeaders($this->headers)->get('/api/content/destinations?city=London');

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
    public function test_hotel_destination_with_empty_parameter_method_response_400()
    {
        $response_detail = $this->withHeaders($this->headers)->get('/api/content/destinations?city=');

        $response_detail
            ->assertStatus(400)
            ->assertJson([
                'error' => 'Invalid city',
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_hotel_destination_without_parameter_method_response_true()
    {
        $response_detail = $this->withHeaders($this->headers)->get('/api/content/destinations');

        $response_detail
            ->assertStatus(400)
            ->assertJson([
                'error' => 'Invalid city',
            ]);
    }
}
