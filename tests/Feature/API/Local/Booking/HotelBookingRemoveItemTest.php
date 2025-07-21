<?php

use Illuminate\Support\Str;

test('hotel booking remove item method response 200', function () {
    $createBooking = $this->createHotelBooking();

    $bookingId = $createBooking['booking_id'];

    $firstBookingItem = $createBooking['booking_items'][0];

    $bookingRemoveItemResponse = $this->withHeaders($this->headers)
        ->deleteJson("api/booking/remove-item?booking_id=$bookingId&booking_item=$firstBookingItem");

    $bookingRemoveItemResponse->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'result' => [
                    'booking_id' => $bookingId,
                    'booking_item' => $firstBookingItem,
                    'status' => 'Item removed from cart.',
                ],
            ],
            'message' => 'success',
        ]);
});

test('hotel booking remove attempting to remove previously removed item again method response 200', function () {
    $createBooking = $this->createHotelBooking();

    $bookingId = $createBooking['booking_id'];

    $firstBookingItem = $createBooking['booking_items'][0];

    $this->withHeaders($this->headers)
        ->deleteJson("api/booking/remove-item?booking_id=$bookingId&booking_item=$firstBookingItem");

    $bookingRemoveItemResponse = $this->withHeaders($this->headers)
        ->deleteJson("api/booking/remove-item?booking_id=$bookingId&booking_item=$firstBookingItem");

    $bookingRemoveItemResponse->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'result' => [
                    'booking_id' => $bookingId,
                    'booking_item' => $firstBookingItem,
                    'status' => 'This item is not in the cart',
                ],
            ],
            'message' => 'success',
        ]);
});

test('hotel booking remove item with non existent booking id and booking item method response 400', function () {
    $nonExistentBookingId = Str::uuid()->toString();

    $createBooking = $this->createHotelBooking();

    $bookingItem = $createBooking['booking_items'][0];

    $bookingRemoveItemResponse = $this->withHeaders($this->headers)
        ->deleteJson("api/booking/remove-item?booking_id=$nonExistentBookingId&booking_item=$bookingItem");

    $bookingRemoveItemResponse->assertStatus(400)
        ->assertJson([
            'error' => 'Invalid booking_id',
        ]);
});

test('hotel booking remove item with non existent booking item and booking id method response 400', function () {
    $nonExistentBookingItem = Str::uuid()->toString();

    $createBooking = $this->createHotelBooking();

    $bookingId = $createBooking['booking_id'];

    $bookingRemoveItemResponse = $this->withHeaders($this->headers)
        ->deleteJson("api/booking/remove-item?booking_id=$bookingId&booking_item=$nonExistentBookingItem");

    $bookingRemoveItemResponse->assertStatus(400)
        ->assertJson([
            'error' => 'Invalid booking_item',
        ]);
});

test('hotel booking remove item with empty booking item and correct booking id method response 400', function () {
    $createBooking = $this->createHotelBooking();

    $bookingId = $createBooking['booking_id'];

    $bookingRemoveItemResponse = $this->withHeaders($this->headers)
        ->deleteJson("api/booking/remove-item?booking_id=$bookingId&booking_item=");

    $bookingRemoveItemResponse->assertStatus(400)
        ->assertJson([
            'error' => 'Invalid type',
        ]);
});

test('hotel booking remove item with missed booking id and correct booking item method response 400', function () {
    $createBooking = $this->createHotelBooking();

    $bookingItem = $createBooking['booking_items'][0];

    $bookingRemoveItemResponse = $this->withHeaders($this->headers)
        ->deleteJson("api/booking/remove-item?booking_item=$bookingItem");

    $bookingRemoveItemResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'booking_id' => [
                    'The booking id field is required.',
                ],
            ],
        ]);
});

test('hotel booking remove item with missed booking item and correct booking id method response 400', function () {
    $createBooking = $this->createHotelBooking();

    $bookingId = $createBooking['booking_id'];

    $bookingRemoveItemResponse = $this->withHeaders($this->headers)
        ->deleteJson("api/booking/remove-item?booking_id=$bookingId");

    $bookingRemoveItemResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'booking_item' => [
                    'The booking item field is required.',
                ],
            ],
        ]);
});