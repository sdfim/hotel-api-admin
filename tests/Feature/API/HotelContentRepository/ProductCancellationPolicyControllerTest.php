<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\ProductCancellationPolicy;

// uses(RefreshDatabase::class);

test('index', function () {
    ProductCancellationPolicy::factory()->count(3)->create();
    $response = test()->getJson('api/repo/product-cancellation-policy');
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id', 'product_id', 'name', 'start_date', 'expiration_date', 'manipulable_price_type', 'price_value', 'price_value_type', 'price_value_target',
            ],
        ],
        'message',
    ]);
    $this->assertDatabaseCount('pd_product_cancellation_policies', 3);
});

test('store', function () {
    $data = ProductCancellationPolicy::factory()->make()->toArray();
    $response = test()->postJson('api/repo/product-cancellation-policy', $data);
    $response->assertStatus(201);
    $response->assertJsonStructure([
        'data' => [
            'id', 'product_id', 'name', 'start_date', 'expiration_date', 'manipulable_price_type', 'price_value', 'price_value_type', 'price_value_target',
        ],
        'message',
    ]);
    $this->assertDatabaseHas('pd_product_cancellation_policies', $data);
});

test('show', function () {
    $cancellationPolicy = ProductCancellationPolicy::factory()->create();
    $response = test()->getJson("api/repo/product-cancellation-policy/{$cancellationPolicy->id}");
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'id', 'product_id', 'name', 'start_date', 'expiration_date', 'manipulable_price_type', 'price_value', 'price_value_type', 'price_value_target',
        ],
        'message',
    ]);
    $this->assertDatabaseHas('pd_product_cancellation_policies', $cancellationPolicy->toArray());
});

test('update', function () {
    $cancellationPolicy = ProductCancellationPolicy::factory()->create();
    $data = ProductCancellationPolicy::factory()->make(['product_id' => $cancellationPolicy->product_id])->toArray();
    $response = test()->putJson("api/repo/product-cancellation-policy/{$cancellationPolicy->id}", $data);
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'id', 'product_id', 'name', 'start_date', 'expiration_date', 'manipulable_price_type', 'price_value', 'price_value_type', 'price_value_target',
        ],
        'message',
    ]);
    $this->assertDatabaseHas('pd_product_cancellation_policies', $data);
});

test('destroy', function () {
    $cancellationPolicy = ProductCancellationPolicy::factory()->create();
    $response = test()->deleteJson("api/repo/product-cancellation-policy/{$cancellationPolicy->id}");
    $response->assertStatus(204);
    $this->assertDatabaseMissing('pd_product_cancellation_policies', ['id' => $cancellationPolicy->id]);
});