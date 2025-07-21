<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\HotelRoom;

// uses(RefreshDatabase::class);

test('index', function () {
    HotelRoom::factory()->count(3)->create();
    $response = test()->getJson('api/repo/hotel-rooms');
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id', 'hotel_id', 'name', 'external_code', 'description',
            ],
        ],
        'message',
    ]);
    $this->assertDatabaseCount('pd_hotel_rooms', 3);
});

test('store', function () {
    $data = HotelRoom::factory()->make()->toArray();
    $response = test()->postJson('api/repo/hotel-rooms', $data);
    $response->assertStatus(201);
    $response->assertJsonStructure([
        'data' => [
            'id', 'hotel_id', 'name', 'external_code', 'description',
        ],
        'message',
    ]);
    $this->assertDatabaseHas('pd_hotel_rooms', $data);
});

test('show', function () {
    $hotelRoom = HotelRoom::factory()->create();
    $response = test()->getJson("api/repo/hotel-rooms/{$hotelRoom->id}");
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'id', 'hotel_id', 'name', 'external_code', 'description',
        ],
        'message',
    ]);
    $this->assertDatabaseHas('pd_hotel_rooms', $hotelRoom->toArray());
});

test('update', function () {
    $hotelRoom = HotelRoom::factory()->create();
    $data = HotelRoom::factory()->make(['hotel_id' => $hotelRoom->hotel_id])->toArray();
    $response = test()->putJson("api/repo/hotel-rooms/{$hotelRoom->id}", $data);
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'id', 'hotel_id', 'name', 'external_code', 'description',
        ],
        'message',
    ]);
    $this->assertDatabaseHas('pd_hotel_rooms', $data);
});

test('destroy', function () {
    $hotelRoom = HotelRoom::factory()->create();
    $response = test()->deleteJson("api/repo/hotel-rooms/{$hotelRoom->id}");
    $response->assertStatus(204);
    $this->assertSoftDeleted('pd_hotel_rooms', ['id' => $hotelRoom->id]);
});