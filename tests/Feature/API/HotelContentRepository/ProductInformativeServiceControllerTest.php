<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\ProductInformativeService;

// uses(RefreshDatabase::class);

test('index', function () {
    ProductInformativeService::factory()->count(3)->create();
    $response = test()->getJson('api/repo/product-informative-services');
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'product_id', 'service_id', 'cost'],
        ],
        'message',
    ]);
    $this->assertDatabaseCount('pd_product_informative_services', 3);
});

test('store', function () {
    $data = ProductInformativeService::factory()->make()->toArray();
    $response = test()->postJson('api/repo/product-informative-services', $data);
    $response->assertStatus(201);
    $response->assertJsonStructure([
        'data' => ['id', 'product_id', 'service_id', 'cost'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_product_informative_services', $data);
});

test('show', function () {
    $service = ProductInformativeService::factory()->create();
    $response = test()->getJson("api/repo/product-informative-services/{$service->id}");
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => ['id', 'product_id', 'service_id', 'cost'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_product_informative_services', $service->toArray());
});

test('update', function () {
    $service = ProductInformativeService::factory()->create();
    $data = ProductInformativeService::factory()->make(['product_id' => $service->product_id])->toArray();
    $response = test()->putJson("api/repo/product-informative-services/{$service->id}", $data);
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => ['id', 'product_id', 'service_id', 'cost'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_product_informative_services', $data);
});

test('destroy', function () {
    $service = ProductInformativeService::factory()->create();
    $response = test()->deleteJson("api/repo/product-informative-services/{$service->id}");
    $response->assertStatus(204);
    $this->assertDatabaseMissing('pd_product_informative_services', ['id' => $service->id]);
});