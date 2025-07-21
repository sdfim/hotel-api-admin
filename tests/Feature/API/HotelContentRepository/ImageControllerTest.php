<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\Image;

// uses(RefreshDatabase::class);

test('index', function () {
    Image::factory()->count(3)->create();
    $response = test()->getJson('api/repo/images');
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id',  'image_url', 'tag', 'weight', 'section_id'],
        ],
        'message',
    ]);
    $this->assertDatabaseCount('pd_images', 3);
});

test('store', function () {
    $data = Image::factory()->make()->toArray();
    $response = test()->postJson('api/repo/images', $data);
    $response->assertStatus(201);
    $response->assertJsonStructure([
        'data' => ['id',  'image_url', 'tag', 'weight', 'section_id'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_images', $data);
});

test('show', function () {
    $image = Image::factory()->create();
    $response = test()->getJson("api/repo/images/{$image->id}");
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => ['id',  'image_url', 'tag', 'weight', 'section_id'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_images', $image->toArray());
});

test('update', function () {
    $image = Image::factory()->create();
    $data = Image::factory()->make()->toArray();
    $response = test()->putJson("api/repo/images/{$image->id}", $data);
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => ['id',  'image_url', 'tag', 'weight', 'section_id'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_images', $data);
});

test('destroy', function () {
    $image = Image::factory()->create();
    $response = test()->deleteJson("api/repo/images/{$image->id}");
    $response->assertStatus(204);
    $this->assertDatabaseMissing('pd_images', ['id' => $image->id]);
});