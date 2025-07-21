<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\ProductDescriptiveContentSection;

// uses(RefreshDatabase::class);

test('index', function () {
    ProductDescriptiveContentSection::factory()->count(3)->create();
    $response = test()->getJson('api/repo/product-descriptive-content-sections');
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'product_id', 'section_name', 'start_date', 'end_date', 'descriptive_type_id', 'value'],
        ],
        'message',
    ]);
    $this->assertDatabaseCount('pd_product_descriptive_content_sections', 3);
});

test('store', function () {
    $data = ProductDescriptiveContentSection::factory()->make()->toArray();
    $response = test()->postJson('api/repo/product-descriptive-content-sections', $data);
    $response->assertStatus(201);
    $response->assertJsonStructure([
        'data' => ['id', 'product_id', 'section_name', 'start_date', 'end_date', 'descriptive_type_id', 'value'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_product_descriptive_content_sections', $data);
});

test('show', function () {
    $content = ProductDescriptiveContentSection::factory()->create();
    $response = test()->getJson("api/repo/product-descriptive-content-sections/{$content->id}");
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => ['id', 'product_id', 'section_name', 'start_date', 'end_date', 'descriptive_type_id', 'value'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_product_descriptive_content_sections', $content->toArray());
});

test('update', function () {
    $content = ProductDescriptiveContentSection::factory()->create();
    $data = ProductDescriptiveContentSection::factory()->make()->toArray();
    $response = test()->putJson("api/repo/product-descriptive-content-sections/{$content->id}", $data);
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => ['id', 'product_id', 'section_name', 'start_date', 'end_date', 'descriptive_type_id', 'value'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_product_descriptive_content_sections', $data);
});

test('destroy', function () {
    $content = ProductDescriptiveContentSection::factory()->create();
    $response = test()->deleteJson("api/repo/product-descriptive-content-sections/{$content->id}");
    $response->assertStatus(204);
    $this->assertDatabaseMissing('pd_product_descriptive_content_sections', ['id' => $content->id]);
});