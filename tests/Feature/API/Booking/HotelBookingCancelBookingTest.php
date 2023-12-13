<?php

namespace Feature\API\Booking;

use Carbon\Carbon;
use Faker\Provider\en_UG\Address;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\Feature\API\Booking\HotelBookingApiTestCase;

class HotelBookingCancelBookingTest extends HotelBookingApiTestCase
{
    /**
     * @test
     * @return void
     */
    public function test_hotel_booking_cancel_booking_method_response_200(): void
    {
        $hotelBook = $this->createHotelBookingAndAddPassengersToBookingItemAndHotelBook();

        $bookingId = $hotelBook['booking_id'];
        $bookingItem = $hotelBook['booking_item'];

        $cancelBookingResponse = $this->withHeaders($this->headers)
            ->deleteJson("api/booking/cancel-booking?booking_id=$bookingId&booking_it=$bookingItem");

        $cancelBookingResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'result' => [
                        [
                            'booking_item' => $bookingItem,
                            'status' => 'Room is already cancelled.'
                        ]
                    ]
                ],
                'message' => 'success'
            ]);
    }

}
