<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelWebFinder;

// uses(RefreshDatabase::class);

test('can list hotels', function () {
    Hotel::factory()->count(3)->create();
    $response = test()->getJson('api/repo/hotels');
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id', 'weight', 'sale_type', 'address', 'star_rating', 'num_rooms', 'room_images_source_id', 'hotel_board_basis',
            ],
        ],
        'message',
    ]);
    $this->assertDatabaseCount('pd_hotels', 3);
});

test('can create hotel', function () {
    $data = Hotel::factory()->make()->toArray();
    $response = test()->postJson('api/repo/hotels', $data);
    $response->assertStatus(201);
    $response->assertJsonStructure([
        'data' => [
            'id', 'weight', 'sale_type', 'address', 'star_rating', 'num_rooms', 'room_images_source_id', 'hotel_board_basis',
        ],
        'message',
    ]);
    //        $this->assertDatabaseHas('pd_hotels', $data);
});

test('can show hotel', function () {
    $hotel = Hotel::factory()->create();
    $response = test()->getJson("api/repo/hotels/{$hotel->id}");
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'data' => [
            '*' => [
                'id', 'weight', 'sale_type', 'address', 'star_rating', 'num_rooms',
                'room_images_source_id', 'hotel_board_basis', 'travel_agent_commission',
            ],
        ],
        'message',
    ]);
    //        $this->assertDatabaseHas('pd_hotels', $hotel->toArray());
});

test('can update hotel', function () {
    $hotel = Hotel::factory()->create();
    $data = Hotel::factory()->make(['id' => $hotel->id])->toArray();
    $response = test()->putJson("api/repo/hotels/{$hotel->id}", $data);
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'id', 'weight', 'sale_type', 'address', 'star_rating', 'num_rooms', 'room_images_source_id', 'hotel_board_basis',
        ],
        'message',
    ]);
    //        $this->assertDatabaseHas('pd_hotels', $data);
});

test('can delete hotel', function () {
    $hotel = Hotel::factory()->create();
    $response = test()->deleteJson("api/repo/hotels/{$hotel->id}");
    $response->assertStatus(204);
    $this->assertDatabaseMissing('pd_hotels', ['id' => $hotel->id]);
});

test('can attach web finder to hotel', function () {
    $hotel = Hotel::factory()->create();
    $webFinder = HotelWebFinder::factory()->create();

    $response = test()->postJson("api/repo/hotels/{$hotel->id}/attach-web-finder", [
        'web_finder_id' => $webFinder->id,
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'base_url', 'finder', 'example'],
        ],
        'message',
    ]);
    $this->assertDatabaseHas('pd_hotel_web_finder_hotel', [
        'hotel_id' => $hotel->id,
        'web_finder_id' => $webFinder->id,
    ]);
});

test('can detach web finder from hotel', function () {
    $hotel = Hotel::factory()->create();
    $webFinder = HotelWebFinder::factory()->create();
    $hotel->webFinders()->attach($webFinder->id);

    $response = test()->postJson("api/repo/hotels/{$hotel->id}/detach-web-finder", [
        'web_finder_id' => $webFinder->id,
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'message',
    ]);

    $this->assertDatabaseMissing('pd_hotel_web_finder_hotel', [
        'hotel_id' => $hotel->id,
        'web_finder_id' => $webFinder->id,
    ]);
});