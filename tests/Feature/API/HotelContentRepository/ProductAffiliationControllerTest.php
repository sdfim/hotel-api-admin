<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\ProductAffiliation;

// uses(RefreshDatabase::class);

test('can create product affiliation', function () {
    $data = ProductAffiliation::factory()->make()->toArray();
    $response = test()->postJson('api/repo/product-affiliations', $data);
    $response->assertStatus(201)->assertJsonFragment($data);
    $this->assertDatabaseHas('pd_product_affiliations', $data);
});

test('can update product affiliation', function () {
    $productAffiliation = ProductAffiliation::factory()->create();
    $data = ProductAffiliation::factory()->make(['product_id' => $productAffiliation->product_id])->toArray();
    $response = test()->putJson("api/repo/product-affiliations/{$productAffiliation->id}", $data);
    $response->assertStatus(200)->assertJsonFragment($data);
    $this->assertDatabaseHas('pd_product_affiliations', $data);
});

test('can delete product affiliation', function () {
    $productAffiliation = ProductAffiliation::factory()->create();
    $response = test()->deleteJson("api/repo/product-affiliations/{$productAffiliation->id}");
    $response->assertStatus(204);
    $this->assertDatabaseMissing('pd_product_affiliations', ['id' => $productAffiliation->id]);
});

test('can list pd product affiliations', function () {
    $productAffiliations = ProductAffiliation::factory()->count(3)->create();
    $response = test()->getJson('api/repo/product-affiliations');
    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('can show product affiliation', function () {
    $productAffiliation = ProductAffiliation::factory()->create();
    $response = test()->getJson("api/repo/product-affiliations/{$productAffiliation->id}");
    $response->assertStatus(200)
        ->assertJsonFragment([
            'id' => $productAffiliation->id,
        ]);
});