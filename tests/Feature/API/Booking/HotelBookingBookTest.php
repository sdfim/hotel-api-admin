<?php

namespace Tests\Feature\API\Booking;

use App\Models\User;
use Feature\API\ApiTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HotelBookingBookTest extends ApiTestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @return void
     */
    public function test_book_method_response()
    {
        $headers = $this->getHeader();

        $response = $this->withHeaders($headers)
            ->postJson('/api/booking/book', ['booking_id' => 'd491dd2b-56fc-45e5-a7e3-3ed5a9ffb023']);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }
}
