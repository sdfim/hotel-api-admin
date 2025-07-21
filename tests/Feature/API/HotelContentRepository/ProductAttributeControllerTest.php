<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\ProductAttribute;

// uses(RefreshDatabase::class);

test('can list product attributes', function () {
    ProductAttribute::factory()->count(3)->create();
    $response = test()->getJson('api/repo/product-attributes');
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'product_id', 'config_attribute_id'],
        ],
        'message',
    ]);
});

test('can create product attribute', function () {
    $data = ProductAttribute::factory()->make()->toArray();
    $response = test()->postJson('api/repo/product-attributes', $data);
    $response->assertStatus(201);
    $response->assertJsonStructure([
        'data' => ['id', 'product_id', 'config_attribute_id'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_product_attributes', $data);
});

test('can show product attribute', function () {
    $attribute = ProductAttribute::factory()->create();
    $response = test()->getJson("api/repo/product-attributes/{$attribute->id}");
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => ['id', 'product_id', 'config_attribute_id'],
        'message',
    ]);
});

test('can update product attribute', function () {
    $attribute = ProductAttribute::factory()->create();
    $data = ProductAttribute::factory()->make(['product_id' => $attribute->product_id])->toArray();
    $response = test()->putJson("api/repo/product-attributes/{$attribute->id}", $data);
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => ['id', 'product_id', 'config_attribute_id'],
            'message',
        ]);
    $this->assertDatabaseHas('pd_product_attributes', $data);
});

test('can delete product attribute', function () {
    $attribute = ProductAttribute::factory()->create();
    $response = test()->deleteJson("api/repo/product-attributes/{$attribute->id}");
    $response->assertStatus(204);
    $this->assertDatabaseMissing('pd_product_attributes', ['id' => $attribute->id]);
});