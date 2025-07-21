<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\KeyMapping;

// uses(RefreshDatabase::class);

test('index', function () {
    KeyMapping::factory()->count(3)->create();
    $response = test()->getJson('api/repo/key-mappings');
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'product_id', 'key_id', 'key_mapping_owner_id'],
        ],
        'message',
    ]);
    $this->assertDatabaseCount('pd_key_mapping', 3);
});

test('store', function () {
    $data = KeyMapping::factory()->make()->toArray();
    $response = test()->postJson('api/repo/key-mappings', $data);
    $response->assertStatus(201);
    $response->assertJsonStructure([
        'data' => ['id', 'product_id', 'key_id', 'key_mapping_owner_id'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_key_mapping', $data);
});

test('show', function () {
    $keyMapping = KeyMapping::factory()->create();
    $response = test()->getJson("api/repo/key-mappings/{$keyMapping->id}");
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => ['id', 'product_id', 'key_id', 'key_mapping_owner_id'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_key_mapping', $keyMapping->toArray());
});

test('update', function () {
    $keyMapping = KeyMapping::factory()->create();
    $data = KeyMapping::factory()->make()->toArray();
    $response = test()->putJson("api/repo/key-mappings/{$keyMapping->id}", $data);
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => ['id', 'product_id', 'key_id', 'key_mapping_owner_id'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_key_mapping', $data);
});

test('destroy', function () {
    $keyMapping = KeyMapping::factory()->create();
    $response = test()->deleteJson("api/repo/key-mappings/{$keyMapping->id}");
    $response->assertStatus(204);
    $this->assertDatabaseMissing('pd_key_mapping', ['id' => $keyMapping->id]);
});