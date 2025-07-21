<?php

use Illuminate\Support\Str;

test('hotel booking add first item method response 200', function () {
    $pricingSearchRequestResponse = $this->getHotelPricingSearchData();

    $bookingItems = $this->getBookingItemsFromPricingSearchResult($pricingSearchRequestResponse);

    $bookingAddItemResponse = $this->withHeaders($this->headers)
        ->postJson("api/booking/add-item?booking_item=$bookingItems[0]");

    $bookingId = $bookingAddItemResponse->json('data.booking_id');

    $bookingAddItemResponse->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'booking_id' => $bookingId,
            ],
            'message' => 'success',
        ]);
});

test('hotel booking add item to an existing booking method response 200', function () {
    $createBooking = $this->createHotelBooking();

    $bookingId = $createBooking['booking_id'];

    $secondBookingItem = $createBooking['booking_items'][rand(1, count($createBooking['booking_items']))];

    $bookingAddItemResponse = $this->withHeaders($this->headers)
        ->postJson("api/booking/add-item?booking_item=$secondBookingItem&booking_id=$bookingId");

    $bookingAddItemResponse->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'booking_id' => $createBooking['booking_id'],
            ],
            'message' => 'success',
        ]);
});

test('hotel booking add previously deleted item again method response 200', function () {
    $createBooking = $this->createHotelBooking();

    $bookingId = $createBooking['booking_id'];

    $firstBookingItem = $createBooking['booking_items'][0];

    $this->withHeaders($this->headers)
        ->deleteJson("api/booking/remove-item?booking_id=$bookingId&booking_item=$firstBookingItem");

    $bookingAddItemResponse = $this->withHeaders($this->headers)
        ->postJson("api/booking/add-item?booking_item=$firstBookingItem&booking_id=$bookingId");

    $bookingAddItemResponse->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'booking_id' => $bookingId,
            ],
            'message' => 'success',
        ]);
});

test('hotel booking add item with non existent booking item and missed booking id method response 400', function () {
    $nonExistentBookingItem = Str::uuid()->toString();

    $bookingAddItemResponse = $this->withHeaders($this->headers)
        ->postJson("api/booking/add-item?booking_item=$nonExistentBookingItem");

    $bookingAddItemResponse->assertStatus(400)
        ->assertJson([
            'error' => 'Invalid booking_item',
        ]);
});

test('hotel booking add item to non existent booking id and correct booking item method response 400', function () {
    $nonExistentBookingId = Str::uuid()->toString();

    $createBooking = $this->createHotelBooking();

    $bookingItem = $createBooking['booking_items'][0];

    $bookingAddItemResponse = $this->withHeaders($this->headers)
        ->postJson("api/booking/add-item?booking_item=$bookingItem&booking_id=$nonExistentBookingId");

    $bookingAddItemResponse->assertStatus(400)
        ->assertJson([
            'error' => 'Invalid booking_id',
        ]);
});

test('hotel booking add item with empty booking item and missed booking id method response 400', function () {
    $bookingAddItemResponse = $this->withHeaders($this->headers)
        ->postJson('api/booking/add-item?booking_item=');

    $bookingAddItemResponse->assertStatus(400)
        ->assertJson([
            'error' => 'Invalid type',
        ]);
});

test('hotel booking add item with empty booking id and missed booking item method response 400', function () {
    $bookingAddItemResponse = $this->withHeaders($this->headers)
        ->postJson('api/booking/add-item?booking_id=');

    $bookingAddItemResponse->assertStatus(400)
        ->assertJson([
            'error' => 'Invalid booking_id',

        ]);
});

test('hotel booking add item without parameters method response 400', function () {
    $bookingAddItemResponse = $this->withHeaders($this->headers)
        ->postJson('api/booking/add-item');

    $bookingAddItemResponse->assertStatus(400)
        ->assertJson([
            'error' => 'Invalid type',
        ]);
});