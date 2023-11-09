<?php

namespace Tests\Feature\API\Booking;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Modules\API\BookingAPI\BookingApiHandlers\BookApiHandler;
use Tests\TestCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;

class HotelBookingBookTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_method_response()
    {
        $request = Request::create('/book', 'POST', ['booking_id' => '1949b7ad-559e-4826-b6c9-746b94022ec7']);
        $controller = new BookApiHandler();

		// Add a bearer token to the request
        $token = 'your_bearer_token_here';
        $request->headers->add(['Authorization' => 'Bearer ' . $token]);

        $response = $controller->book($request);

        $response->assertStatus(200); 

        $response->assertJson(['status' => 'success']); 
        $response->assertJsonStructure(['data']);
    }
}
