<?php

namespace Tests\Feature\API\Local\Booking;

use Illuminate\Support\Str;

class HotelBookingCancelBookingTest extends HotelBookingApiTestCase
{
    #[Test]
    public function test_hotel_booking_cancel_booking_method_response_200(): void
    {
        $hotelBook = $this->createHotelBookingAndAddPassengersToBookingItemAndHotelBook();

        $bookingId = $hotelBook['booking_id'];
        $bookingItem = $hotelBook['booking_item'];

        $cancelBookingResponse = $this->withHeaders($this->headers)
            ->deleteJson("api/booking/cancel-booking?booking_id=$bookingId");

        $cancelBookingResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'result' => [
                        [
                            'booking_item' => $bookingItem,
                            'status' => 'Room canceled.',
                        ],
                    ],
                ],
                'message' => 'success',
            ]);
    }

    #[Test]
    public function test_hotel_booking_cancel_booking_item_method_response_200(): void
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
                            'status' => 'Room canceled.',
                        ],
                    ],
                ],
                'message' => 'success',
            ]);
    }

    #[Test]
    public function test_hotel_booking_attempting_to_cancel_previously_cancelled_booking_again_method_response_200(): void
    {
        $hotelBook = $this->createHotelBookingAndAddPassengersToBookingItemAndHotelBook();

        $bookingId = $hotelBook['booking_id'];
        $bookingItem = $hotelBook['booking_item'];

        $this->withHeaders($this->headers)
            ->deleteJson("api/booking/cancel-booking?booking_id=$bookingId");

        $cancelBookingResponse = $this->withHeaders($this->headers)
            ->deleteJson("api/booking/cancel-booking?booking_id=$bookingId");

        $cancelBookingResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'result' => [
                        [
                            'booking_item' => $bookingItem,
                            'status' => 'Room is already cancelled.',
                        ],
                    ],
                ],
                'message' => 'success',
            ]);
    }

    #[Test]
    public function test_hotel_booking_attempting_to_cancel_previously_cancelled_booking_item_again_method_response_200(): void
    {
        $hotelBook = $this->createHotelBookingAndAddPassengersToBookingItemAndHotelBook();

        $bookingId = $hotelBook['booking_id'];

        $bookingItem = $hotelBook['booking_item'];

        $this->withHeaders($this->headers)
            ->deleteJson("api/booking/cancel-booking?booking_id=$bookingId&booking_it=$bookingItem");

        $cancelBookingResponse = $this->withHeaders($this->headers)
            ->deleteJson("api/booking/cancel-booking?booking_id=$bookingId&booking_it=$bookingItem");

        $cancelBookingResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'result' => [
                        [
                            'booking_item' => $bookingItem,
                            'status' => 'Room is already cancelled.',
                        ],
                    ],
                ],
                'message' => 'success',
            ]);
    }

    #[Test]
    public function test_hotel_booking_cancel_item_with_non_existent_booking_id_and_booking_item_method_response_400(): void
    {
        $nonExistentBookingId = Str::uuid()->toString();

        $hotelBook = $this->createHotelBookingAndAddPassengersToBookingItemAndHotelBook();

        $bookingItem = $hotelBook['booking_item'];

        $bookingCancelItemResponse = $this->withHeaders($this->headers)
            ->deleteJson("api/booking/cancel-booking?booking_id=$nonExistentBookingId&booking_item=$bookingItem");

        $bookingCancelItemResponse->assertStatus(400)
            ->assertJson([
                'error' => 'Invalid booking_id',
            ]);
    }

    #[Test]
    public function test_hotel_booking_cancel_item_with_non_existent_booking_item_and_booking_id_method_response_400(): void
    {
        $nonExistentBookingItem = Str::uuid()->toString();

        $hotelBook = $this->createHotelBookingAndAddPassengersToBookingItemAndHotelBook();

        $bookingId = $hotelBook['booking_id'];

        $bookingCancelItemResponse = $this->withHeaders($this->headers)
            ->deleteJson("api/booking/cancel-booking?booking_id=$bookingId&booking_item=$nonExistentBookingItem");

        $bookingCancelItemResponse->assertStatus(400)
            ->assertJson([
                'error' => 'Invalid booking_item',
            ]);
    }

    #[Test]
    public function test_hotel_booking_cancel_item_with_missed_booking_id_and_correct_booking_item_method_response_400(): void
    {
        $hotelBook = $this->createHotelBookingAndAddPassengersToBookingItemAndHotelBook();

        $bookingItem = $hotelBook['booking_item'];

        $bookingCancelItemResponse = $this->withHeaders($this->headers)
            ->deleteJson("api/booking/cancel-booking?booking_item=$bookingItem");

        $bookingCancelItemResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_id' => [
                        'The booking id field is required.',
                    ],
                ],
            ]);
    }

    #[Test]
    public function test_hotel_booking_cancel_item_without_parameters_method_response_400(): void
    {
        $bookingCancelItemResponse = $this->withHeaders($this->headers)
            ->deleteJson('api/booking/cancel-booking');

        $bookingCancelItemResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'booking_id' => [
                        'The booking id field is required.',
                    ],
                ],
            ]);
    }
}
