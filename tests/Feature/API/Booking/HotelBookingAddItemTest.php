<?php

namespace Tests\Feature\API\Booking;

use Feature\API\ApiTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HotelBookingAddItemTest extends ApiTestCase
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
    public function test_hotel_booking_add_item_method_response_200(): void
    {
        $jsonData = [];
        $response = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $jsonData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }
}
