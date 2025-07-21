<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\HotelWebFinder;

// uses(RefreshDatabase::class);

test('index', function () {
    HotelWebFinder::factory()->count(3)->create();
    $response = test()->getJson('api/repo/hotel-web-finders');
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'base_url', 'finder', 'website', 'example'],
        ],
        'message',
    ]);
    $this->assertDatabaseCount('pd_hotel_web_finders', 3);
});

test('store', function () {
    $data = HotelWebFinder::factory()->make()->toArray();
    $response = test()->postJson('api/repo/hotel-web-finders', $data);
    $response->assertStatus(201);
    $response->assertJsonStructure([
        'data' => ['id', 'base_url', 'finder', 'website', 'example'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_hotel_web_finders', $data);
});

test('show', function () {
    $webFinder = HotelWebFinder::factory()->create();
    $response = test()->getJson("api/repo/hotel-web-finders/{$webFinder->id}");
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => ['id', 'base_url', 'finder', 'website', 'example'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_hotel_web_finders', $webFinder->toArray());
});

test('update', function () {
    $webFinder = HotelWebFinder::factory()->create();
    $data = HotelWebFinder::factory()->make()->toArray();
    $response = test()->putJson("api/repo/hotel-web-finders/{$webFinder->id}", $data);
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => ['id', 'base_url', 'finder', 'website', 'example'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_hotel_web_finders', $data);
});

test('destroy', function () {
    $webFinder = HotelWebFinder::factory()->create();
    $response = test()->deleteJson("api/repo/hotel-web-finders/{$webFinder->id}");
    $response->assertStatus(204);
    $this->assertDatabaseMissing('pd_hotel_web_finders', ['id' => $webFinder->id]);
});