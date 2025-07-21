<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\HotelWebFinderUnit;

// uses(RefreshDatabase::class);

test('index', function () {
    HotelWebFinderUnit::factory()->count(3)->create();
    $response = test()->getJson('api/repo/hotel-web-finder-units');
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'field', 'value', 'web_finder_id', 'type'],
        ],
    ]);
    $this->assertDatabaseCount('pd_hotel_web_finder_units', 3);
});

test('store', function () {
    $data = HotelWebFinderUnit::factory()->make()->toArray();
    $response = test()->postJson('api/repo/hotel-web-finder-units', $data);
    $response->assertStatus(201);
    $response->assertJsonStructure([
        'data' => ['id', 'field', 'value', 'web_finder_id', 'type'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_hotel_web_finder_units', $data);
});

test('show', function () {
    $unit = HotelWebFinderUnit::factory()->create();
    $response = test()->getJson("api/repo/hotel-web-finder-units/{$unit->id}");
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => ['id', 'field', 'value', 'web_finder_id', 'type'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_hotel_web_finder_units', $unit->toArray());
});

test('update', function () {
    $unit = HotelWebFinderUnit::factory()->create();
    $data = HotelWebFinderUnit::factory()->make()->toArray();
    $response = test()->putJson("api/repo/hotel-web-finder-units/{$unit->id}", $data);
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => ['id', 'field', 'value', 'web_finder_id', 'type'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_hotel_web_finder_units', $data);
});

test('destroy', function () {
    $unit = HotelWebFinderUnit::factory()->create();
    $response = test()->deleteJson("api/repo/hotel-web-finder-units/{$unit->id}");
    $response->assertStatus(204);
    $this->assertDatabaseMissing('pd_hotel_web_finder_units', ['id' => $unit->id]);
});