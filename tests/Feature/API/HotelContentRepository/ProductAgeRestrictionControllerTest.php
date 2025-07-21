<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\ProductAgeRestriction;

// uses(RefreshDatabase::class);

test('can create product age restriction', function () {
    $data = ProductAgeRestriction::factory()->make()->toArray();
    $response = test()->postJson('api/repo/age-restrictions', $data);
    $response->assertStatus(201)
        ->assertJsonFragment($data);
    $this->assertDatabaseHas('pd_product_age_restrictions', $data);
});

test('can update product age restriction', function () {
    $ageRestriction = ProductAgeRestriction::factory()->create();
    $data = ProductAgeRestriction::factory()->make([
        'product_id' => $ageRestriction->product_id,
        'restriction_type' => $ageRestriction->restriction_type,
    ])->toArray();

    $response = test()->putJson("api/repo/age-restrictions/{$ageRestriction->id}", $data);
    $response->assertStatus(200)
        ->assertJsonFragment($data);
    $this->assertDatabaseHas('pd_product_age_restrictions', $data);
});

test('can delete product age restriction', function () {
    $ageRestriction = ProductAgeRestriction::factory()->create();
    $response = test()->deleteJson("api/repo/age-restrictions/{$ageRestriction->id}");
    $response->assertStatus(204);
    $this->assertDatabaseMissing('pd_product_age_restrictions', ['id' => $ageRestriction->id]);
});

test('can list product age restrictions', function () {
    $ageRestrictions = ProductAgeRestriction::factory()->count(3)->create();
    $response = test()->getJson('api/repo/age-restrictions');
    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('can show product age restriction', function () {
    $ageRestriction = ProductAgeRestriction::factory()->create();
    $response = test()->getJson("api/repo/age-restrictions/{$ageRestriction->id}");
    $response->assertStatus(200)
        ->assertJsonFragment([
            'id' => $ageRestriction->id,
            'product_id' => $ageRestriction->product_id,
            'restriction_type' => $ageRestriction->restriction_type,
        ]);
});