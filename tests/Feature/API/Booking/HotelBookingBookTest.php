<?php

namespace Tests\Feature\API\Booking;

use Feature\API\ApiTestCase;

class HotelBookingBookTest extends ApiTestCase
{
    /**
     * @test
     * @return void
     */
    public function test_book_method_response()
    {
        $response = $this->withHeaders($this->headers)
            ->postJson('/api/booking/book', ['booking_id' => 'd491dd2b-56fc-45e5-a7e3-3ed5a9ffb023']);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }
}
