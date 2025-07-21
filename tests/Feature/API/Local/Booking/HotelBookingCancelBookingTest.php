<?php

use Illuminate\Support\Str;

test('hotel booking cancel booking method response 200', function () {
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
});

test('hotel booking cancel booking item method response 200', function () {
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
});

test('hotel booking attempting to cancel previously cancelled booking again method response 200', function () {
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
});

test('hotel booking attempting to cancel previously cancelled booking item again method response 200', function () {
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
});

test('hotel booking cancel item with non existent booking id and booking item method response 400', function () {
    $nonExistentBookingId = Str::uuid()->toString();

    $hotelBook = $this->createHotelBookingAndAddPassengersToBookingItemAndHotelBook();

    $bookingItem = $hotelBook['booking_item'];

    $bookingCancelItemResponse = $this->withHeaders($this->headers)
        ->deleteJson("api/booking/cancel-booking?booking_id=$nonExistentBookingId&booking_item=$bookingItem");

    $bookingCancelItemResponse->assertStatus(400)
        ->assertJson([
            'error' => 'Invalid booking_id',
        ]);
});

test('hotel booking cancel item with non existent booking item and booking id method response 400', function () {
    $nonExistentBookingItem = Str::uuid()->toString();

    $hotelBook = $this->createHotelBookingAndAddPassengersToBookingItemAndHotelBook();

    $bookingId = $hotelBook['booking_id'];

    $bookingCancelItemResponse = $this->withHeaders($this->headers)
        ->deleteJson("api/booking/cancel-booking?booking_id=$bookingId&booking_item=$nonExistentBookingItem");

    $bookingCancelItemResponse->assertStatus(400)
        ->assertJson([
            'error' => 'Invalid booking_item',
        ]);
});

test('hotel booking cancel item with missed booking id and correct booking item method response 400', function () {
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
});

test('hotel booking cancel item without parameters method response 400', function () {
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
});