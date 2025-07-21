<?php

namespace Tests\Feature\API\BookingFlow;

use App\Models\Configurations\ConfigServiceType;

test('search', function () {
    $this->stage = 0;
    $this->search();
});

test('add booking item', function () {
    $this->add_booking_item();
})->depends('search');

test('add insurance', function () {
    $request = [
        'booking_item' => $this->bookingItem,
        'services' => [
            [
                'service_id' => ConfigServiceType::first()->id,
                'cost' => 100,
            ],
        ],
    ];
    $response = $this->request()->post(route('attachService'), $request);

    $response->assertStatus(201);
    $response->assertJson(['success' => true]);
})->depends('add booking item');

test('delete insurance', function () {
    $request = [
        'booking_item' => $this->bookingItem,
        'services' => [
            [
                'service_id' => ConfigServiceType::first()->id,
                'cost' => 100,
            ],
        ],
    ];
    $response = $this->request()->post(route('detachService'), $request);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
})->depends('add insurance');