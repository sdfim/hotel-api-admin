<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\ImageSection;

// uses(RefreshDatabase::class);

test('index', function () {
    ImageSection::factory()->count(3)->create();
    $response = test()->getJson('api/repo/image-sections');
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'name'],
        ],
        'message',
    ]);
    $this->assertDatabaseCount('pd_image_sections', 3);
});

test('store', function () {
    $data = ImageSection::factory()->make()->toArray();
    $response = test()->postJson('api/repo/image-sections', $data);
    $response->assertStatus(201);
    $response->assertJsonStructure([
        'data' => ['id', 'name'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_image_sections', $data);
});

test('show', function () {
    $hotelImageSection = ImageSection::factory()->create();
    $response = test()->getJson("api/repo/image-sections/{$hotelImageSection->id}");
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => ['id', 'name'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_image_sections', $hotelImageSection->toArray());
});

test('update', function () {
    $hotelImageSection = ImageSection::factory()->create();
    $data = ImageSection::factory()->make()->toArray();
    $response = test()->putJson("api/repo/image-sections/{$hotelImageSection->id}", $data);
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => ['id', 'name'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_image_sections', $data);
});

test('destroy', function () {
    $hotelImageSection = ImageSection::factory()->create();
    $response = test()->deleteJson("api/repo/image-sections/{$hotelImageSection->id}");
    $response->assertStatus(204);
    $this->assertDatabaseMissing('pd_image_sections', ['id' => $hotelImageSection->id]);
});