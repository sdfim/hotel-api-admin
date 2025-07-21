<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\Image;
use Modules\HotelContentRepository\Models\ImageGallery;

// uses(RefreshDatabase::class);

test('index', function () {
    ImageGallery::factory()->count(3)->create();
    $response = test()->getJson('api/repo/image-galleries');
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'gallery_name', 'description'],
        ],
        'message',
    ]);
    $this->assertDatabaseCount('pd_image_galleries', 3);
});

test('store', function () {
    $data = ImageGallery::factory()->make()->toArray();
    $response = test()->postJson('api/repo/image-galleries', $data);
    $response->assertStatus(201);
    $response->assertJsonStructure([
        'data' => ['id', 'gallery_name', 'description'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_image_galleries', $data);
});

test('show', function () {
    $gallery = ImageGallery::factory()->create();
    $response = test()->getJson("api/repo/image-galleries/{$gallery->id}");
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => ['id', 'gallery_name', 'description'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_image_galleries', $gallery->toArray());
});

test('update', function () {
    $gallery = ImageGallery::factory()->create();
    $data = ImageGallery::factory()->make()->toArray();
    $response = test()->putJson("api/repo/image-galleries/{$gallery->id}", $data);
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => ['id', 'gallery_name', 'description'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_image_galleries', $data);
});

test('destroy', function () {
    $gallery = ImageGallery::factory()->create();
    $response = test()->deleteJson("api/repo/image-galleries/{$gallery->id}");
    $response->assertStatus(204);
    $this->assertDatabaseMissing('pd_image_galleries', ['id' => $gallery->id]);
});

test('attach image', function () {
    $gallery = ImageGallery::factory()->create();
    $image = Image::factory()->create();

    $response = test()->postJson("api/repo/image-galleries/{$gallery->id}/attach-image", [
        'image_id' => $image->id,
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'id', 'gallery_name', 'description', 'images' => [
                '*' => ['id', 'image_url', 'tag', 'weight', 'section_id'],
            ],
        ],
        'message',
    ]);
    $this->assertDatabaseHas('pd_gallery_images', [
        'gallery_id' => $gallery->id,
        'image_id' => $image->id,
    ]);
});

test('detach image', function () {
    $gallery = ImageGallery::factory()->create();
    $image = Image::factory()->create(); // Assuming you have an Image factory
    $gallery->images()->attach($image->id);

    $response = test()->postJson("api/repo/image-galleries/{$gallery->id}/detach-image", [
        'image_id' => $image->id,
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'id', 'gallery_name', 'description', 'images' => [],
        ],
        'message',
    ]);
    $this->assertDatabaseMissing('pd_gallery_images', [
        'gallery_id' => $gallery->id,
        'image_id' => $image->id,
    ]);
});