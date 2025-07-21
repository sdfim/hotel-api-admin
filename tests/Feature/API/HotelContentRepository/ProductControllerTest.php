<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\ImageGallery;
use Modules\HotelContentRepository\Models\Product;

// uses(RefreshDatabase::class);

test('can list products', function () {
    Product::factory()->count(3)->create();
    $response = test()->getJson('api/repo/products');
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id', 'vendor_id', 'product_type', 'name', 'verified',
                'content_source_id', 'property_images_source_id', 'default_currency',
                'website', 'lat', 'lng', 'related_id', 'related_type',
            ],
        ],
        'message',
    ]);
    $this->assertDatabaseCount('pd_products', 3);
});

test('can create product', function () {
    $data = Product::factory()->make()->toArray();
    $response = test()->postJson('api/repo/products', $data);
    $response->assertStatus(201);
    $response->assertJsonStructure([
        'data' => [
            'id', 'vendor_id', 'product_type', 'name', 'verified',
            'content_source_id', 'property_images_source_id', 'default_currency',
            'website', 'lat', 'lng', 'related_id', 'related_type',
        ],
        'message',
    ]);
    $this->assertDatabaseHas('pd_products', $data);
});

test('can show product', function () {
    $product = Product::factory()->create();
    $response = test()->getJson("api/repo/products/{$product->id}");
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id', 'vendor_id', 'product_type', 'name', 'verified',
                'content_source_id', 'property_images_source_id', 'default_currency',
                'website', 'lat', 'lng', 'related_id', 'related_type',
            ],
        ],
        'message',
    ]);
    $this->assertDatabaseHas('pd_products', $product->toArray());
});

test('can update product', function () {
    $product = Product::factory()->create();
    $data = Product::factory()->make(['id' => $product->id])->toArray();
    $response = test()->putJson("api/repo/products/{$product->id}", $data);
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'id', 'vendor_id', 'product_type', 'name', 'verified',
            'content_source_id', 'property_images_source_id', 'default_currency',
            'website', 'lat', 'lng', 'related_id', 'related_type',
        ],
        'message',
    ]);
    $this->assertDatabaseHas('pd_products', $data);
});

test('can delete product', function () {
    $product = Product::factory()->create();
    $response = test()->deleteJson("api/repo/products/{$product->id}");
    $response->assertStatus(204);
    $this->assertDatabaseMissing('pd_products', ['id' => $product->id]);
});

test('can attach gallery to hotel', function () {
    $product = Product::factory()->create();
    $gallery = ImageGallery::factory()->create();

    $response = test()->postJson("api/repo/products/{$product->id}/attach-gallery", [
        'gallery_id' => $gallery->id,
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'gallery_name', 'description'],
        ],
        'message',
    ]);
    $this->assertDatabaseHas('pd_product_gallery', [
        'product_id' => $product->id,
        'gallery_id' => $gallery->id,
    ]);
});

test('can detach gallery from hotel', function () {
    $product = Product::factory()->create();
    $gallery = ImageGallery::factory()->create();
    $product->galleries()->attach($gallery->id);

    $response = test()->postJson("api/repo/products/{$product->id}/detach-gallery", [
        'gallery_id' => $gallery->id,
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'message',
    ]);

    $this->assertDatabaseMissing('pd_product_gallery', [
        'product_id' => $product->id,
        'gallery_id' => $gallery->id,
    ]);
});