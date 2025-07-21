<?php

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;

test('hotel booking add passengers method response 200', function () {
    $createBooking = $this->createHotelBooking();

    $bookingId = $createBooking['booking_id'];
    $bookingItem = $createBooking['booking_items'][0];
    $occupancy = $createBooking['hotel_pricing_request_data']['occupancy'];

    $addPassengersData = $this->generateAddPassengersData($bookingItem, $occupancy);

    $addPassengersResponse = $this->withHeaders($this->headers)
        ->postJson("api/booking/add-passengers?booking_id=$bookingId", $addPassengersData);

    $addPassengersResponse->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'result' => [
                    '*' => [
                        'booking_id',
                        'booking_item',
                        'status',
                    ],
                ],
            ],
            'message',
        ])
        ->assertJson([
            'success' => true,
            'message' => 'success',
        ]);
});

test('hotel booking add passengers again method response 200', function () {
    $createBooking = $this->createHotelBooking();

    $bookingId = $createBooking['booking_id'];

    $bookingItem = $createBooking['booking_items'][0];

    $occupancy = $createBooking['hotel_pricing_request_data']['occupancy'];

    $addPassengersData = $this->generateAddPassengersData($bookingItem, $occupancy);

    $this->withHeaders($this->headers)
        ->postJson("api/booking/add-passengers?booking_id=$bookingId", $addPassengersData);

    $addPassengersResponse = $this->withHeaders($this->headers)
        ->postJson("api/booking/add-passengers?booking_id=$bookingId", $addPassengersData);

    $addPassengersResponse->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'result' => [
                    '*' => [
                        'booking_id',
                        'booking_item',
                        'status',
                    ],
                ],
            ],
            'message',
        ])
        ->assertJson([
            'success' => true,
            'message' => 'success',
        ]);
});

test('hotel booking add passengers with empty booking id method response 400', function () {
    $addPassengersResponse = $this->withHeaders($this->headers)
        ->postJson('api/booking/add-passengers?booking_id=');

    $addPassengersResponse->assertStatus(400)
        ->assertJson([
            'error' => 'Invalid booking_id',
        ]);
});

test('hotel booking add passengers with non existent booking id method response 400', function () {
    $nonExistentBookingId = Str::uuid()->toString();

    $addPassengersResponse = $this->withHeaders($this->headers)
        ->postJson("api/booking/add-passengers?booking_id=$nonExistentBookingId");

    $addPassengersResponse->assertStatus(400)
        ->assertJson([
            'error' => 'Invalid booking_id',
        ]);
});

test('hotel booking add passengers without parameters method response 400', function () {
    $addPassengersResponse = $this->withHeaders($this->headers)
        ->postJson('api/booking/add-passengers');

    $addPassengersResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'booking_id' => [
                    'The booking id field is required.',
                ],
            ],
        ]);
});

test('hotel booking add passengers with children ages mismatch method response 400', function () {
    $addPassengersResponse = sendAddPassengersRequestWithIncorrectData(['children_ages_mismatch']);

    $addPassengersResponse->assertStatus(400)
        ->assertJsonStructure([
            'success',
            'error' => [
                'type',
                'booking_item',
                'search_id',
                'room',
                'children_ages_in_search',
                'children_ages_in_query',
            ],
            'message',
        ])
        ->assertJson([
            'success' => false,
            'message' => 'failed',
        ]);
});

test('hotel booking add passengers with number of children mismatch method response 400', function () {
    $addPassengersResponse = sendAddPassengersRequestWithIncorrectData(['number_of_children_mismatch'], true);

    $addPassengersResponse->assertStatus(400)
        ->assertJsonStructure([
            'success',
            'error' => [
                'type',
                'booking_item',
                'search_id',
                'room',
                'number_of_children_in_search',
                'number_of_children_in_query',
            ],
            'message',
        ])
        ->assertJson([
            'success' => false,
            'message' => 'failed',
        ]);
});

test('hotel booking add passengers with number of adults mismatch method response 400', function () {
    $addPassengersResponse = sendAddPassengersRequestWithIncorrectData(['number_of_adults_mismatch']);

    $addPassengersResponse->assertStatus(400)
        ->assertJsonStructure([
            'success',
            'error' => [
                'type',
                'booking_item',
                'search_id',
                'room',
                'number_of_adults_in_search',
                'number_of_adults_in_query',
            ],
            'message',
        ])
        ->assertJson([
            'success' => false,
            'message' => 'failed',
        ]);
});

test('hotel booking add passengers with missed passenger title method response 400', function () {
    $addPassengersResponse = sendAddPassengersRequestWithIncorrectData(['missed_passenger_title']);

    $addPassengersResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'passengers.0.title' => [
                    'The passengers.0.title field is required.',
                ],
            ],
        ]);
});

test('hotel booking add passengers with empty passenger title method response 400', function () {
    $addPassengersResponse = sendAddPassengersRequestWithIncorrectData(['empty_passenger_title']);

    $addPassengersResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'passengers.0.title' => [
                    'The passengers.0.title field is required.',
                ],
            ],
        ]);
});

test('hotel booking add passengers with missed passenger given name method response 400', function () {
    $addPassengersResponse = sendAddPassengersRequestWithIncorrectData(['missed_passenger_given_name']);

    $addPassengersResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'passengers.0.given_name' => [
                    'The passengers.0.given_name field is required.',
                ],
            ],
        ]);
});

test('hotel booking add passengers with empty passenger given name method response 400', function () {
    $addPassengersResponse = sendAddPassengersRequestWithIncorrectData(['empty_passenger_given_name']);

    $addPassengersResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'passengers.0.given_name' => [
                    'The passengers.0.given_name field is required.',
                ],
            ],
        ]);
});

test('hotel booking add passengers with missed passenger family name method response 400', function () {
    $addPassengersResponse = sendAddPassengersRequestWithIncorrectData(['missed_passenger_family_name']);

    $addPassengersResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'passengers.0.family_name' => [
                    'The passengers.0.family_name field is required.',
                ],
            ],
        ]);
});

test('hotel booking add passengers with empty passenger family name method response 400', function () {
    $addPassengersResponse = sendAddPassengersRequestWithIncorrectData(['empty_passenger_family_name']);

    $addPassengersResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'passengers.0.family_name' => [
                    'The passengers.0.family_name field is required.',
                ],
            ],
        ]);
});

test('hotel booking add passengers with missed passenger date of birth method response 400', function () {
    $addPassengersResponse = sendAddPassengersRequestWithIncorrectData(['missed_passenger_date_of_birth']);

    $addPassengersResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'passengers.0.date_of_birth' => [
                    'The passengers.0.date_of_birth field is required.',
                ],
            ],
        ]);
});

test('hotel booking add passengers with empty passenger date of birth method response 400', function () {
    $addPassengersResponse = sendAddPassengersRequestWithIncorrectData(['empty_passenger_date_of_birth']);

    $addPassengersResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'passengers.0.date_of_birth' => [
                    'The passengers.0.date_of_birth field is required.',
                ],
            ],
        ]);
});

test('hotel booking add passengers with incorrect passenger date of birth method response 400', function () {
    $addPassengersResponse = sendAddPassengersRequestWithIncorrectData(['incorrect_passenger_date_of_birth']);

    $addPassengersResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'passengers.0.date_of_birth' => [
                    'The passengers.0.date_of_birth does not match the format Y-m-d.',
                ],
            ],
        ]);
});

test('hotel booking add passengers with missed passenger booking items method response 400', function () {
    $addPassengersResponse = sendAddPassengersRequestWithIncorrectData(['missed_passenger_booking_items']);

    $addPassengersResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'passengers.0.booking_items' => [
                    'The passengers.0.booking_items field is required.',
                ],
            ],
        ]);
});

test('hotel booking add passengers with empty passenger booking items method response 400', function () {
    $addPassengersResponse = sendAddPassengersRequestWithIncorrectData(['empty_passenger_booking_items']);

    $addPassengersResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'passengers.0.booking_items' => [
                    'The passengers.0.booking_items field is required.',
                ],
            ],
        ]);
});

test('hotel booking add passengers with missed passenger booking item method response 400', function () {
    $addPassengersResponse = sendAddPassengersRequestWithIncorrectData(['missed_passenger_booking_item']);

    $addPassengersResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'passengers.0.booking_items.0.booking_item' => [
                    'The passengers.0.booking_items.0.booking_item field is required.',
                ],
            ],
        ]);
});

test('hotel booking add passengers with empty passenger booking item method response 400', function () {
    $addPassengersResponse = sendAddPassengersRequestWithIncorrectData(['empty_passenger_booking_item']);

    $addPassengersResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'passengers.0.booking_items.0.booking_item' => [
                    'The passengers.0.booking_items.0.booking_item field is required.',
                ],
            ],
        ]);
});

test('hotel booking add passengers with non existent passenger booking item method response 400', function () {
    $addPassengersResponse = sendAddPassengersRequestWithIncorrectData(['non_existent_passenger_booking_item']);

    $addPassengersResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'booking_item' => 'Invalid booking_item',
            ],
            'message' => 'failed',
        ]);
});

test('hotel booking add passengers with invalid uuid passenger booking item method response 400', function () {
    $addPassengersResponse = sendAddPassengersRequestWithIncorrectData(['invalid_uuid_passenger_booking_item']);

    $addPassengersResponse->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'passengers.0.booking_items.0.booking_item' => [
                    'The passengers.0.booking_items.0.booking_item must be a valid UUID.',
                ],
            ],
        ]);
});

/**
 * @param  bool  $withChildren  if true then children will definitely be generated, otherwise randomly true/false
 */
function sendAddPassengersRequestWithIncorrectData(array $keysToFail = [], bool $withChildren = false): TestResponse
{
    $createBooking = $this->createHotelBooking($withChildren);

    $bookingId = $createBooking['booking_id'];
    $bookingItem = $createBooking['booking_items'][0];
    $occupancy = $createBooking['hotel_pricing_request_data']['occupancy'];

    $incorrectPassengersData = addPassengersData($bookingItem, $occupancy, $keysToFail);

    return $this->withHeaders($this->headers)
        ->postJson("api/booking/add-passengers?booking_item=$bookingItem&booking_id=$bookingId", $incorrectPassengersData);
}

/**
 * @param  array  $keysToFail
 *                             Possible values:
 *                             - 'children_ages_mismatch': Indicates that there is a mismatch in the ages of the children passengers.
 *                             - 'number_of_children_mismatch': Indicates that there is a mismatch in the number of children passengers.
 *                             - 'number_of_adults_mismatch': Indicates that there is a mismatch in the number of adult passengers.
 *                             - 'missed_passenger_title': Indicates that the 'title' key is missing for the first passenger in the $data array.
 *                             - 'empty_passenger_title': Indicates that the 'title' key is empty for the first passenger in the $data array.
 *                             - 'missed_passenger_given_name': Indicates that the 'given_name' key is missing for the first passenger in the $data array.
 *                             - 'empty_passenger_given_name': Indicates that the 'given_name' key is empty for the first passenger in the $data array.
 *                             - 'missed_passenger_family_name': Indicates that the 'family_name' key is missing for the first passenger in the $data array.
 *                             - 'empty_passenger_family_name': Indicates that the 'family_name' key is empty for the first passenger in the $data array.
 *                             - 'missed_passenger_date_of_birth': Indicates that the 'date_of_birth' key is missing for the first passenger in the $data array.
 *                             - 'empty_passenger_date_of_birth': Indicates that the 'date_of_birth' key is empty for the first passenger in the $data array.
 *                             - 'incorrect_passenger_date_of_birth': Indicates that the 'date_of_birth' key for the first passenger in the $data array has an incorrect format.
 *                             - 'missed_passenger_booking_items': Indicates that the 'booking_items' key is missing for the first passenger in the $data array.
 *                             - 'empty_passenger_booking_items': Indicates that the 'booking_items' key is empty for the first passenger in the $data array.
 *                             - 'missed_passenger_booking_item': Indicates that the 'booking_item' key is missing for the first booking item of the first passenger in the $data array.
 *                             - 'empty_passenger_booking_item': Indicates that the 'booking_item' key is empty for the first booking item of the first passenger in the $data array.
 *                             - 'non_existent_passenger_booking_item': Indicates that the 'booking_item' key for the first booking item of the first passenger in the $data array contains a non-existent booking item.
 *                             - 'invalid_uuid_passenger_booking_item': Indicates that the 'booking_item' key for the first booking item of the first passenger in the $data array contains an invalid UUID.
 */
function addPassengersData(string $bookingItem, array $occupancy, array $keysToFail = []): array
{
    $data = $this->generateAddPassengersData($bookingItem, $occupancy);

    if (count($keysToFail) > 0) {
        if (array_intersect($keysToFail, ['children_ages_mismatch', 'number_of_children_mismatch', 'number_of_adults_mismatch'])) {
            $now = Carbon::now();
            $currentYear = $now->copy()->year;
            $minChildAge = $now->copy()->subYears(16)->year;

            if (in_array('children_ages_mismatch', $keysToFail)) {
                foreach ($data['passengers'] as &$passenger) {
                    $dateOfBirth = Carbon::createFromFormat('Y-m-d', $passenger['date_of_birth']);
                    if ($dateOfBirth->year >= $minChildAge && $dateOfBirth->year <= $currentYear) {
                        $passenger['date_of_birth'] = $dateOfBirth->subYears(rand(1, 4))->toDateString();
                    }
                }
                unset($passenger);
            }
            if (in_array('number_of_children_mismatch', $keysToFail)) {
                foreach ($data['passengers'] as $passengerId => $passenger) {
                    $dateOfBirth = Carbon::createFromFormat('Y-m-d', $passenger['date_of_birth']);
                    if ($dateOfBirth->year >= $minChildAge && $dateOfBirth->year <= $currentYear) {
                        unset($data['passengers'][$passengerId]);
                    }
                }
            }
            if (in_array('number_of_adults_mismatch', $keysToFail)) {
                foreach ($data['passengers'] as $passengerId => $passenger) {
                    $dateOfBirth = Carbon::createFromFormat('Y-m-d', $passenger['date_of_birth']);
                    if (floor($dateOfBirth->diffInYears($now, true)) >= 18) {
                        unset($data['passengers'][$passengerId]);
                    }
                }
            }
        }

        if (in_array('missed_passenger_title', $keysToFail)) {
            unset($data['passengers'][0]['title']);
        }
        if (in_array('empty_passenger_title', $keysToFail)) {
            $data['passengers'][0]['title'] = '';
        }
        if (in_array('missed_passenger_given_name', $keysToFail)) {
            unset($data['passengers'][0]['given_name']);
        }
        if (in_array('empty_passenger_given_name', $keysToFail)) {
            $data['passengers'][0]['given_name'] = '';
        }
        if (in_array('missed_passenger_family_name', $keysToFail)) {
            unset($data['passengers'][0]['family_name']);
        }
        if (in_array('empty_passenger_family_name', $keysToFail)) {
            $data['passengers'][0]['family_name'] = '';
        }
        if (in_array('missed_passenger_date_of_birth', $keysToFail)) {
            unset($data['passengers'][0]['date_of_birth']);
        }
        if (in_array('empty_passenger_date_of_birth', $keysToFail)) {
            $data['passengers'][0]['date_of_birth'] = '';
        }
        if (in_array('incorrect_passenger_date_of_birth', $keysToFail)) {
            $data['passengers'][0]['date_of_birth'] = $this->faker->randomNumber(8);
        }
        if (in_array('missed_passenger_booking_items', $keysToFail)) {
            unset($data['passengers'][0]['booking_items']);
        }
        if (in_array('empty_passenger_booking_items', $keysToFail)) {
            $data['passengers'][0]['booking_items'] = [];
        }
        if (in_array('missed_passenger_booking_item', $keysToFail)) {
            unset($data['passengers'][0]['booking_items'][0]['booking_item']);
        }
        if (in_array('empty_passenger_booking_item', $keysToFail)) {
            $data['passengers'][0]['booking_items'][0]['booking_item'] = '';
        }
        if (in_array('non_existent_passenger_booking_item', $keysToFail)) {
            $nonExistentBookingItem = Str::uuid();
            $data['passengers'][0]['booking_items'][0]['booking_item'] = $nonExistentBookingItem;
        }
        if (in_array('invalid_uuid_passenger_booking_item', $keysToFail)) {
            $invalidUuid = Str::uuid().'t';
            $data['passengers'][0]['booking_items'][0]['booking_item'] = $invalidUuid;
        }
    }

    return $data;
}