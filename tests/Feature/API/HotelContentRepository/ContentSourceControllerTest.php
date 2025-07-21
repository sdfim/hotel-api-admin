<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\ContentSource;

// uses(RefreshDatabase::class);

test('index', function () {
    ContentSource::factory()->count(3)->create();
    $response = test()->getJson('api/repo/content-sources');
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'name'],
        ],
        'message',
    ]);
    $this->assertDatabaseCount('pd_content_sources', 3);
});

test('store', function () {
    $data = ContentSource::factory()->make()->toArray();
    $response = test()->postJson('api/repo/content-sources', $data);
    $response->assertStatus(201);
    $response->assertJsonStructure([
        'data' => ['id', 'name'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_content_sources', $data);
});

test('show', function () {
    $contentSource = ContentSource::factory()->create();
    $response = test()->getJson("api/repo/content-sources/{$contentSource->id}");
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => ['id', 'name'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_content_sources', $contentSource->toArray());
});

test('update', function () {
    $contentSource = ContentSource::factory()->create();
    $data = ContentSource::factory()->make()->toArray();
    $response = test()->putJson("api/repo/content-sources/{$contentSource->id}", $data);
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => ['id', 'name'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_content_sources', $data);
});

test('destroy', function () {
    $contentSource = ContentSource::factory()->create();
    $response = test()->deleteJson("api/repo/content-sources/{$contentSource->id}");
    $response->assertStatus(204);
    $this->assertDatabaseMissing('pd_content_sources', ['id' => $contentSource->id]);
});