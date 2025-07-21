<?php

use App\Models\Channel;
use App\Models\Reservation;

test('reservation index is opening', function () {
    $this->get('/admin/reservations')
        ->assertStatus(200);
});

test('possibility of showing an existing reservation record', function () {
    $channel = Channel::factory()->create();

    $reservations = Reservation::factory()->create([
        'channel_id' => $channel->id,
    ]);

    $this->get("/admin/reservations/{$reservations->id}")
        ->assertStatus(200);
});
