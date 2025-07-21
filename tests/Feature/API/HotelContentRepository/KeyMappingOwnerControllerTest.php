<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\KeyMappingOwner;

// uses(RefreshDatabase::class);

test('index', function () {
    KeyMappingOwner::factory()->count(3)->create();
    $response = test()->getJson('api/repo/key-mapping-owners');
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'name'],
        ],
        'message',
    ]);
    $this->assertDatabaseCount('pd_key_mapping_owners', 3);
});

test('store', function () {
    $data = KeyMappingOwner::factory()->make()->toArray();
    $response = test()->postJson('api/repo/key-mapping-owners', $data);
    $response->assertStatus(201);
    $response->assertJsonStructure([
        'data' => ['id', 'name'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_key_mapping_owners', $data);
});

test('show', function () {
    $keyMappingOwner = KeyMappingOwner::factory()->create();
    $response = test()->getJson("api/repo/key-mapping-owners/{$keyMappingOwner->id}");
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => ['id', 'name'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_key_mapping_owners', $keyMappingOwner->toArray());
});

test('update', function () {
    $keyMappingOwner = KeyMappingOwner::factory()->create();
    $data = KeyMappingOwner::factory()->make()->toArray();
    $response = test()->putJson("api/repo/key-mapping-owners/{$keyMappingOwner->id}", $data);
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => ['id', 'name'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_key_mapping_owners', $data);
});

test('destroy', function () {
    $keyMappingOwner = KeyMappingOwner::factory()->create();
    $response = test()->deleteJson("api/repo/key-mapping-owners/{$keyMappingOwner->id}");
    $response->assertStatus(204);
    $this->assertDatabaseMissing('pd_key_mapping_owners', ['id' => $keyMappingOwner->id]);
});