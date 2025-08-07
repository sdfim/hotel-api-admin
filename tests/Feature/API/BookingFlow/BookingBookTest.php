<?php

namespace Tests\Feature\API\BookingFlow;

test('search', function () {
    $this->stage = 2;
    $this->search();
});

test('add booking item', function () {
    $this->add_booking_item();
})->depends('search');

test('add passengers', function () {
    $this->add_passengers();
})->depends('add booking item');

test('book', function () {
    $response = $this->request()->json('POST', route('book'), $this->requestBookData());
    $response->assertStatus(200);
})->depends('add passengers');

test('cancel', function () {
    $response = $this->request()->json('DELETE', route('cancelBooking'), [
        'booking_id' => $this->bookingId,
    ]);
    $response->assertStatus(200);
})->depends('book');

test('search again', function () {
    $this->stage = 1;
    $this->search();
})->depends('cancel');

test('add booking item again', function () {
    $this->add_booking_item();
})->depends('search again');

test('add passengers again', function () {
    $this->add_passengers();
})->depends('add booking item again');

test('book again', function () {
    $this->test_book();
})->depends('add passengers again');

function requestBookData(): array
{
    return [
        'booking_id' => test()->bookingId,
        'amount_pay' => 'Deposit',
        'booking_contact' => [
            'first_name' => 'Test',
            'last_name' => 'Test',
            'email' => 'test@gmail.com',
            'phone' => [
                'country_code' => '1',
                'area_code' => '487',
                'number' => '5550077',
            ],
            'address' => [
                'line_1' => '5047 Kessler Glens', // $faker->streetAddress(),
                'city' => 'Ortizville', // $faker->city(),
                'state_province_code' => 'VT', // $faker->stateAbbr(),
                'postal_code' => 'mt', // $faker->lexify(str_repeat('?', rand(1, 7))), //$faker->postcode(),
                'country_code' => 'US', // $faker->countryCode(),
            ],
        ],
        'special_requests' => [
            [
                'booking_item' => test()->bookingItem,
                'room' => 1,
                'special_request' => 'Test Booking, please disregard.',
            ],
        ],
        'credit_cards' => [
            [
                'credit_card' => [
                    'cvv' => '123',
                    'number' => 4001919257537193,
                    'card_type' => 'VISA',
                    'name_card' => 'Visa',
                    'expiry_date' => '09/2026',
                    'billing_address' => null,
                ],
                'booking_item' => test()->bookingItem,
            ],
        ],
    ];
}
