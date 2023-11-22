<?php

namespace Tests\Feature\API\Booking;

use Feature\API\ApiTestCase;

class HotelBookingAddItemTest extends ApiTestCase
{
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
