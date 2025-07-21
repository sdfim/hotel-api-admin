<?php

namespace Tests\Feature\API\BookingFlow;

test('search', function () {
    $this->stage = 0;
    $this->search();
});

test('add booking item', function () {
    $this->add_booking_item();
})->depends('search');

test('add passengers', function () {
    $this->add_passengers();
})->depends('add booking item');

test('add insurance', function () {
    $request = [
        'booking_item' => $this->bookingItem,
        'vendor' => 'TripMate',
    ];
    $response = $this->request()->post(route('addInsurance'), $request);

    $response->assertStatus(201);
})->depends('add passengers');

test('delete insurance', function () {
    $request = [
        'booking_item' => $this->bookingItem,
        'vendor' => 'TripMate',
    ];

    $response = $this->request()->delete(route('deleteInsurance'), $request);

    $response->assertStatus(204);
})->depends('add insurance');

test('search again', function () {
    $this->stage = 0;
    $this->search();
})->depends('add booking item');

test('add booking item again', function () {
    $this->add_booking_item();
})->depends('search again');

test('add insurance by booking id', function () {
    $request = [
        'booking_id' => $this->bookingId,
        'vendor' => 'TripMate',
    ];
    $response = $this->request()->post(route('addInsurance'), $request);

    $response->assertStatus(201);
})->depends('search again');

test('delete insurance by booking id', function () {
    $request = [
        'booking_id' => $this->bookingId,
        'vendor' => 'TripMate',
    ];

    $response = $this->request()->delete(route('deleteInsurance'), $request);

    $response->assertStatus(204);
})->depends('add insurance by booking id');