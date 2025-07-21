<?php

use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;

test('hotel booking change booking method response 200', function () {
    $hotelBook = $this->createHotelBookingAndAddPassengersToBookingItemAndHotelBook();

    $bookingId = $hotelBook['booking_id'];

    $bookingItem = $hotelBook['booking_item'];

    $changeBookingData = $this->generateChangeBookingData();

    $changeBookingResponse = $this->withHeaders($this->headers)
        ->putJson("api/booking/change-booking?booking_id=$bookingId&booking_item=$bookingItem", $changeBookingData);

    $changeBookingResponse->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'success',
        ]);
});

test('hotel booking change booking without parameters method response 400', function () {
    $changeBookingResponse = $this->withHeaders($this->headers)
        ->putJson('api/booking/change-booking');

    $changeBookingResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'booking_id' => [
                    'The booking id field is required.',
                ],
                'booking_item' => [
                    'The booking item field is required.',
                ],
            ],
        ]);
});

test('hotel booking change booking with booking id and missed booking item method response 400', function () {
    $hotelBook = $this->createHotelBookingAndAddPassengersToBookingItemAndHotelBook();

    $bookingId = $hotelBook['booking_id'];

    $changeBookingResponse = $this->withHeaders($this->headers)
        ->putJson("api/booking/change-booking?booking_id=$bookingId");

    $changeBookingResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'booking_item' => [
                    'The booking item field is required.',
                ],
            ],
        ]);
});

test('hotel booking change booking with booking item and missed booking id method response 400', function () {
    $hotelBook = $this->createHotelBookingAndAddPassengersToBookingItemAndHotelBook();

    $bookingItem = $hotelBook['booking_item'];

    $changeBookingResponse = $this->withHeaders($this->headers)
        ->putJson("api/booking/change-booking?booking_item=$bookingItem");

    $changeBookingResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'booking_id' => [
                    'The booking id field is required.',
                ],
            ],
        ]);
});

test('hotel booking change booking with non existent booking id and missed booking item method response 400', function () {
    $nonExistentBookingId = Str::uuid();

    $changeBookingResponse = $this->withHeaders($this->headers)
        ->putJson("api/booking/change-booking?booking_id=$nonExistentBookingId");

    $changeBookingResponse->assertStatus(400)
        ->assertJson([
            'error' => 'Invalid booking_id',
        ]);
});

test('hotel booking change booking with non existent booking item and missed booking id method response 400', function () {
    $nonExistentBookingItem = Str::uuid();

    $changeBookingResponse = $this->withHeaders($this->headers)
        ->putJson("api/booking/change-booking?booking_item=$nonExistentBookingItem");

    $changeBookingResponse->assertStatus(400)
        ->assertJson([
            'error' => 'Invalid booking_item',
        ]);
});

test('hotel booking change booking with missed query method response 400', function () {
    $changeBookingResponse = sendChangeBookingWithIncorrectData(['missed_query']);

    $changeBookingResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'query' => [
                    'The query field is required.',
                ],
                'query.given_name' => [
                    'The query.given name field is required.',
                ],
                'query.family_name' => [
                    'The query.family name field is required.',
                ],
                'query.smoking' => [
                    'The query.smoking field is required.',
                ],
            ],
        ]);
});

test('hotel booking change booking with missed query given name method response 400', function () {
    $changeBookingResponse = sendChangeBookingWithIncorrectData(['missed_query_given_name']);

    $changeBookingResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'query.given_name' => [
                    'The query.given name field is required.',
                ],
            ],
        ]);
});

test('hotel booking change booking with empty query given name method response 400', function () {
    $changeBookingResponse = sendChangeBookingWithIncorrectData(['empty_query_given_name']);

    $changeBookingResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'query.given_name' => [
                    'The query.given name field is required.',
                ],
            ],
        ]);
});

test('hotel booking change booking with incorrect query given name method response 400', function () {
    $changeBookingResponse = sendChangeBookingWithIncorrectData(['incorrect_query_given_name']);

    $changeBookingResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'query.given_name' => [
                    'The query.given name must be between 1 and 255 characters.',
                ],
            ],
        ]);
});

test('hotel booking change booking with missed query family name method response 400', function () {
    $changeBookingResponse = sendChangeBookingWithIncorrectData(['missed_query_family_name']);

    $changeBookingResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'query.family_name' => [
                    'The query.family name field is required.',
                ],
            ],
        ]);
});

test('hotel booking change booking with empty query family name method response 400', function () {
    $changeBookingResponse = sendChangeBookingWithIncorrectData(['empty_query_family_name']);

    $changeBookingResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'query.family_name' => [
                    'The query.family name field is required.',
                ],
            ],
        ]);
});

test('hotel booking change booking with incorrect query family name method response 400', function () {
    $changeBookingResponse = sendChangeBookingWithIncorrectData(['incorrect_query_family_name']);

    $changeBookingResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'query.family_name' => [
                    'The query.family name must be between 1 and 255 characters.',
                ],
            ],
        ]);
});

test('hotel booking change booking with missed query smoking method response 400', function () {
    $changeBookingResponse = sendChangeBookingWithIncorrectData(['missed_query_smoking']);

    $changeBookingResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'query.smoking' => [
                    'The query.smoking field is required.',
                ],
            ],
        ]);
});

test('hotel booking change booking with empty query smoking method response 400', function () {
    $changeBookingResponse = sendChangeBookingWithIncorrectData(['empty_query_smoking']);

    $changeBookingResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'query.smoking' => [
                    'The query.smoking field is required.',
                ],
            ],
        ]);
});

test('hotel booking change booking with incorrect query smoking method response 400', function () {
    $changeBookingResponse = sendChangeBookingWithIncorrectData(['incorrect_query_smoking']);

    $changeBookingResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'query.smoking' => [
                    'The query.smoking field must be true or false.',
                ],
            ],
        ]);
});

test('hotel booking change booking with incorrect query special request method response 400', function () {
    $changeBookingResponse = sendChangeBookingWithIncorrectData(['incorrect_query_special_request']);

    $changeBookingResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'query.special_request' => [
                    'The query.special request must not be greater than 255 characters.',
                ],
            ],
        ]);
});

test('hotel booking change booking with incorrect query loyalty id method response 400', function () {
    $changeBookingResponse = sendChangeBookingWithIncorrectData(['incorrect_query_loyalty_id']);

    $changeBookingResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'query.loyalty_id' => [
                    'The query.loyalty id must not be greater than 10 characters.',
                ],
            ],
        ]);
});

function sendChangeBookingWithIncorrectData(array $keysToFail = []): TestResponse
{
    $hotelBook = $this->createHotelBookingAndAddPassengersToBookingItemAndHotelBook();

    $bookingId = $hotelBook['booking_id'];

    $bookingItem = $hotelBook['booking_item'];

    $wrongChangeBookingData = hotelChangeBookingData($keysToFail);

    return $this->withHeaders($this->headers)
        ->putJson("api/booking/change-booking?booking_id=$bookingId&booking_item=$bookingItem", $wrongChangeBookingData);
}

/**
 * @param  array  $keysToFail  An array of keys indicating which values to modify or remove.
 *                             Possible values:
 *                             - 'missed_query': Remove the entire 'query' field.
 *                             - 'missed_query_given_name': Remove 'given_name' from 'query'.
 *                             - 'empty_query_given_name': Set an empty string for 'given_name' in 'query'.
 *                             - 'incorrect_query_given_name': Set incorrect data for 'given_name' in 'query'.
 *                             - 'missed_query_family_name': Remove 'family_name' from 'query'.
 *                             - 'empty_query_family_name': Set an empty string for 'family_name' in 'query'.
 *                             - 'incorrect_query_family_name': Set incorrect data for 'family_name' in 'query'.
 *                             - 'missed_query_smoking': Remove 'smoking' from 'query'.
 *                             - 'empty_query_smoking': Set an empty string for 'smoking' in 'query'.
 *                             - 'incorrect_query_smoking': Set incorrect data for 'smoking' in 'query'.
 *                             - 'incorrect_query_special_request': Set incorrect data for 'special_request' in 'query'.
 *                             - 'incorrect_query_loyalty_id': Set incorrect data for 'loyalty_id' in 'query'.
 * @return array The hotel search request data.
 */
function hotelChangeBookingData(array $keysToFail = []): array
{
    $data = $this->generateChangeBookingData();

    if (count($keysToFail) > 0) {
        if (in_array('missed_query', $keysToFail)) {
            unset($data['query']);
        }
        if (in_array('missed_query_given_name', $keysToFail)) {
            unset($data['query']['given_name']);
        }
        if (in_array('empty_query_given_name', $keysToFail)) {
            $data['query']['given_name'] = '';
        }
        if (in_array('incorrect_query_given_name', $keysToFail)) {
            $data['query']['given_name'] = Str::random(256);
        }
        if (in_array('missed_query_family_name', $keysToFail)) {
            unset($data['query']['family_name']);
        }
        if (in_array('empty_query_family_name', $keysToFail)) {
            $data['query']['family_name'] = '';
        }
        if (in_array('incorrect_query_family_name', $keysToFail)) {
            $data['query']['family_name'] = Str::random(256);
        }
        if (in_array('missed_query_smoking', $keysToFail)) {
            unset($data['query']['smoking']);
        }
        if (in_array('empty_query_smoking', $keysToFail)) {
            $data['query']['smoking'] = '';
        }
        if (in_array('incorrect_query_smoking', $keysToFail)) {
            $data['query']['smoking'] = Str::random(4);
        }
        if (in_array('incorrect_query_special_request', $keysToFail)) {
            $data['query']['special_request'] = Str::random(256);
        }
        if (in_array('incorrect_query_loyalty_id', $keysToFail)) {
            $data['query']['loyalty_id'] = Str::random(11);
        }
    }

    return $data;
}