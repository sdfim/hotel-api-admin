<?php

use Illuminate\Support\Carbon;

test('hotel pricing search method response 200', function () {
    $hotelPricingSearchData = hotelPricingSearchData();

    $hotelPricingSearchResponse = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $hotelPricingSearchData);

    $hotelPricingSearchResponse->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);
});

test('hotel pricing search without type method response 400', function () {
    $hotelPricingSearchData = hotelPricingSearchData(['type_missed']);

    $hotelPricingSearchResponse = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $hotelPricingSearchData);

    $hotelPricingSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => 'Invalid type',
        ]);
});

test('hotel pricing search with incorrect type method response 400', function () {
    $hotelPricingSearchData = hotelPricingSearchData(['incorrect_type']);

    $hotelPricingSearchResponse = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $hotelPricingSearchData);

    $hotelPricingSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => 'Invalid type',
        ]);
});

test('hotel pricing search with incorrect currency method response 400', function () {
    $hotelPricingSearchData = hotelPricingSearchData(['incorrect_currency']);

    $hotelPricingSearchResponse = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $hotelPricingSearchData);

    $hotelPricingSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'currency' => [
                    'The selected currency is invalid.',
                ],
            ],
        ]);
});

test('hotel pricing search with incorrect supplier method response 400', function () {
    $hotelPricingSearchData = hotelPricingSearchData(['incorrect_supplier']);

    $hotelPricingSearchResponse = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $hotelPricingSearchData);

    $hotelPricingSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'supplier' => [
                    'The selected supplier is invalid.',
                ],
            ],
        ]);
});

test('hotel pricing search with incorrect check in method response 400', function () {
    $hotelPricingSearchData = hotelPricingSearchData(['incorrect_check_in']);

    $hotelPricingSearchResponse = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $hotelPricingSearchData);

    $hotelPricingSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'checkin' => [
                    'The checkin must be a date after today.',
                ],
            ],
        ]);
});

test('hotel pricing search with incorrect check out method response 400', function () {
    $hotelPricingSearchData = hotelPricingSearchData(['incorrect_check_out']);

    $hotelPricingSearchResponse = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $hotelPricingSearchData);

    $hotelPricingSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'checkout' => [
                    'The checkout must be a date after checkin.',
                ],
            ],
        ]);
});

test('hotel pricing search without check in method response 400', function () {
    $hotelPricingSearchData = hotelPricingSearchData(['check_in_missed']);

    $hotelPricingSearchResponse = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $hotelPricingSearchData);

    $hotelPricingSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'checkin' => [
                    'The checkin field is required.',
                ],
            ],
        ]);
});

test('hotel pricing search without check out method response 400', function () {
    $hotelPricingSearchData = hotelPricingSearchData(['check_out_missed']);

    $hotelPricingSearchResponse = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $hotelPricingSearchData);

    $hotelPricingSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'checkout' => [
                    'The checkout field is required.',
                ],
            ],
        ]);
});

test('hotel pricing search with incorrect destination method response 400', function () {
    $hotelPricingSearchData = hotelPricingSearchData(['incorrect_destination']);

    $hotelPricingSearchResponse = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $hotelPricingSearchData);

    $hotelPricingSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'destination' => [
                    'The destination must be a non-negative integer.',
                ],
            ],
        ]);
});

test('hotel pricing search without destination method response 400', function () {
    $hotelPricingSearchData = hotelPricingSearchData(['missed_destination']);

    $hotelPricingSearchResponse = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $hotelPricingSearchData);

    $hotelPricingSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'destination' => [
                    'The destination field is required.',
                ],
            ],
        ]);
});

test('hotel pricing search with incorrect rating method response 400', function () {
    $hotelPricingSearchData = hotelPricingSearchData(['incorrect_rating']);

    $hotelPricingSearchResponse = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $hotelPricingSearchData);

    $hotelPricingSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'rating' => [
                    'The rating must be between 1 and 5.5.',
                ],
            ],
        ]);
});

test('hotel pricing search with incorrect occupancy method response 400', function () {
    $hotelPricingSearchData = hotelPricingSearchData(['incorrect_occupancy']);

    $hotelPricingSearchResponse = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $hotelPricingSearchData);

    $hotelPricingSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'occupancy.0.adults' => [
                    'The occupancy.0.adults field is required.',
                ],
            ],
        ]);
});

test('hotel pricing search without occupancy method response 400', function () {
    $hotelPricingSearchData = hotelPricingSearchData(['missed_occupancy']);

    $hotelPricingSearchResponse = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $hotelPricingSearchData);

    $hotelPricingSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => [
                'occupancy' => ['The occupancy field is required.'],
            ],
        ]);
});

test('hotel pricing search without occupancy adults method response 400', function () {
    $hotelPricingSearchData = hotelPricingSearchData(['missed_occupancy_adults']);

    $hotelPricingSearchResponse = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $hotelPricingSearchData);

    $error = [];

    foreach ($hotelPricingSearchData['occupancy'] as $index => $room) {
        $errorName = "occupancy.$index.adults";
        $error[$errorName] = ["The $errorName field is required."];
    }

    $hotelPricingSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => $error,
        ]);
});

test('hotel pricing search with incorrect occupancy adults method response 400', function () {
    $hotelPricingSearchData = hotelPricingSearchData(['incorrect_occupancy_adults']);

    $hotelPricingSearchResponse = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $hotelPricingSearchData);

    $error = [];

    foreach ($hotelPricingSearchData['occupancy'] as $index => $room) {
        $errorName = "occupancy.$index.adults";
        $error[$errorName] = ["The $errorName must be between 1 and 9."];
    }

    $hotelPricingSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => $error,
        ]);
});

test('hotel pricing search without children ages method response 400', function () {
    $hotelPricingSearchData = hotelPricingSearchData(['missed_children_ages']);

    $hotelPricingSearchResponse = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $hotelPricingSearchData);

    $error = [];

    foreach ($hotelPricingSearchData['occupancy'] as $index => $room) {
        if (isset($room['children'])) {
            $errorName = "occupancy.$index.children_ages";
            $error[$errorName] = ['The '.str_replace('_', ' ', $errorName).' field is required.'];
            break;
        }
    }

    $hotelPricingSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => $error,
        ]);
});

test('hotel pricing search with incorrect children ages method response 400', function () {
    $hotelPricingSearchData = hotelPricingSearchData(['incorrect_children_ages']);

    $hotelPricingSearchResponse = $this->withHeaders($this->headers)->postJson('/api/pricing/search', $hotelPricingSearchData);

    $error = [];

    foreach ($hotelPricingSearchData['occupancy'] as $index => $room) {
        if (isset($room['children']) && isset($room['children_ages']) && count($room['children_ages']) === 0) {
            $error["occupancy.$index.children_ages"] = ["The occupancy.$index.children ages field is required."];
            break;
        }
    }

    $hotelPricingSearchResponse
        ->assertStatus(400)
        ->assertJson([
            'success' => false,
            'error' => $error,
        ]);
});

/**
 * @param  array  $keysToFail  An array of keys indicating which values to modify or remove.
 *                             Possible values:
 *                             - 'incorrect_type': Set an incorrect value for the 'type' key.
 *                             - 'type_missed': Remove the 'type' key.
 *                             - 'incorrect_currency': Set an incorrect value for the 'currency' key.
 *                             - 'incorrect_supplier': Set an incorrect value for the 'supplier' key.
 *                             - 'incorrect_check_in': Set an incorrect value for the 'checkin' key.
 *                             - 'incorrect_check_out': Set an incorrect value for the 'checkout' key.
 *                             - 'check_in_missed': Remove the 'checkin' key.
 *                             - 'check_out_missed': Remove the 'checkout' key.
 *                             - 'incorrect_destination': Set an incorrect value for the 'destination' key.
 *                             - 'missed_destination': Remove the 'destination' key.
 *                             - 'incorrect_rating': Set an incorrect value for the 'rating' key.
 *                             - 'missed_rating': Remove the 'rating' key.
 *                             - 'incorrect_occupancy': Set an incorrect value for the 'occupancy' key.
 *                             - 'missed_occupancy': Remove the 'occupancy' key.
 *                             - 'missed_occupancy_adults': Remove the 'adults' key from each room in the 'occupancy' array.
 *                             - 'incorrect_occupancy_adults': Set an incorrect value for the 'adults' key in each room of the 'occupancy' array.
 *                             - 'missed_children_ages': Remove the 'children_ages' key from each room in the 'occupancy' array.
 *                             - 'incorrect_children_ages': Set an incorrect value for the 'children_ages' key in each room of the 'occupancy' array.
 * @return array The hotel search request data.
 */
function hotelPricingSearchData(array $keysToFail = []): array
{
    $data = test()->generateHotelPricingSearchData();

    if (count($keysToFail) > 0) {
        $occupancy = &$data['occupancy'];

        if (in_array('incorrect_type', $keysToFail)) {
            $data['type'] = 'wrong_type';
        }
        if (in_array('type_missed', $keysToFail)) {
            unset($data['type']);
        }
        if (in_array('incorrect_currency', $keysToFail)) {
            $data['currency'] = 'Wrong Currency';
        }
        if (in_array('incorrect_supplier', $keysToFail)) {
            $data['supplier'] = 'Wrong Supplier';
        }
        if (in_array('incorrect_check_in', $keysToFail)) {
            $data['checkin'] = Carbon::now()->subDays(5)->toDateString();
        }
        if (in_array('incorrect_check_out', $keysToFail)) {
            $data['checkout'] = Carbon::now()->subDays(2)->toDateString();
        }
        if (in_array('check_in_missed', $keysToFail)) {
            unset($data['checkin']);
        }
        if (in_array('check_out_missed', $keysToFail)) {
            unset($data['checkout']);
        }
        if (in_array('incorrect_destination', $keysToFail)) {
            $data['destination'] = 0;
        }
        if (in_array('missed_destination', $keysToFail)) {
            unset($data['destination']);
        }
        if (in_array('incorrect_rating', $keysToFail)) {
            $data['rating'] = -1;
        }
        if (in_array('missed_rating', $keysToFail)) {
            unset($data['rating']);
        }
        if (in_array('incorrect_occupancy', $keysToFail)) {
            $data['occupancy'] = [[]];
        }
        if (in_array('missed_occupancy', $keysToFail)) {
            unset($data['occupancy']);
        }
        if (in_array('missed_occupancy_adults', $keysToFail)) {
            foreach ($occupancy as &$room) {
                unset($room['adults']);
            }
        }
        if (in_array('incorrect_occupancy_adults', $keysToFail)) {
            foreach ($occupancy as &$room) {
                $room['adults'] = 0;
            }
        }
        if (in_array('missed_children_ages', $keysToFail)) {
            foreach ($occupancy as &$room) {
                if (isset($room['children'], $room['children_ages'])) {
                    unset($room['children_ages']);
                }
            }
        }
        if (in_array('incorrect_children_ages', $keysToFail)) {
            foreach ($occupancy as &$room) {
                if (isset($room['children'], $room['children_ages'])) {
                    $room['children_ages'] = [];
                }
            }
        }
    }

    return $data;
}