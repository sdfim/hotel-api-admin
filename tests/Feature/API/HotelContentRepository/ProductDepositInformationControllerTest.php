<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\ProductDepositInformation;

// uses(RefreshDatabase::class);

test('index', function () {
    ProductDepositInformation::factory()->count(3)->create();
    $response = test()->getJson('api/repo/product-deposit-information');
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id', 'product_id', 'name', 'start_date', 'expiration_date', 'manipulable_price_type', 'price_value', 'price_value_type', 'price_value_target',
            ],
        ],
        'message',
    ]);
    $this->assertDatabaseCount('pd_product_deposit_information', 3);
});

test('store', function () {
    $data = ProductDepositInformation::factory()->make()->toArray();
    $response = test()->postJson('api/repo/product-deposit-information', $data);
    $response->assertStatus(201);
    $response->assertJsonStructure([
        'data' => [
            'id', 'product_id', 'name', 'start_date', 'expiration_date', 'manipulable_price_type', 'price_value', 'price_value_type', 'price_value_target',
        ],
        'message',
    ]);
    $this->assertDatabaseHas('pd_product_deposit_information', $data);
});

test('show', function () {
    $depositInformation = ProductDepositInformation::factory()->create();
    $response = test()->getJson("api/repo/product-deposit-information/{$depositInformation->id}");
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => ['id', 'product_id', 'name', 'start_date', 'expiration_date', 'manipulable_price_type', 'price_value', 'price_value_type', 'price_value_target',
        ],
        'message',
    ]);
    $this->assertDatabaseHas('pd_product_deposit_information', $depositInformation->toArray());
});

test('update', function () {
    $depositInformation = ProductDepositInformation::factory()->create();
    $data = ProductDepositInformation::factory()->make(['product_id' => $depositInformation->product_id])->toArray();
    $response = test()->putJson("api/repo/product-deposit-information/{$depositInformation->id}", $data);
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => ['id', 'product_id', 'name', 'start_date', 'expiration_date', 'manipulable_price_type', 'price_value', 'price_value_type', 'price_value_target',
        ],
        'message',
    ]);
    $this->assertDatabaseHas('pd_product_deposit_information', $data);
});

test('destroy', function () {
    $depositInformation = ProductDepositInformation::factory()->create();
    $response = test()->deleteJson("api/repo/product-deposit-information/{$depositInformation->id}");
    $response->assertStatus(204);
    $this->assertDatabaseMissing('pd_product_deposit_information', ['id' => $depositInformation->id]);
});