<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\ImageGallery;
use Modules\HotelContentRepository\Models\ProductPromotion;

// uses(RefreshDatabase::class);

test('index', function () {
    ProductPromotion::factory()->count(3)->create();
    $response = test()->getJson('api/repo/product-promotions');
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id', 'product_id', 'promotion_name', 'description',
                'validity_start', 'validity_end', 'booking_start', 'booking_end',
                'terms_conditions', 'exclusions', 'min_night_stay', 'max_night_stay',
            ],
        ],
        'message',
    ]);
    $this->assertDatabaseCount('pd_product_promotions', 3);
});

test('store', function () {
    $data = ProductPromotion::factory()->make()->toArray();
    $response = test()->postJson('api/repo/product-promotions', $data);
    $response->assertStatus(201);
    $response->assertJsonStructure([
        'data' => [
            'id', 'rate_code', 'product_id', 'promotion_name', 'description',
            'validity_start', 'validity_end', 'booking_start', 'booking_end',
            'terms_conditions', 'exclusions', 'min_night_stay', 'max_night_stay',
            'not_refundable', 'package',
        ],
        'message',
    ]);
    $this->assertDatabaseHas('pd_product_promotions', $data);
});

test('show', function () {
    $promotion = ProductPromotion::factory()->create();
    $response = test()->getJson("api/repo/product-promotions/{$promotion->id}");
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'id', 'product_id', 'promotion_name', 'description',
            'validity_start', 'validity_end', 'booking_start', 'booking_end',
            'terms_conditions', 'exclusions', 'min_night_stay', 'max_night_stay',
        ],
        'message',
    ]);
    $this->assertDatabaseHas('pd_product_promotions', $promotion->toArray());
});

test('update', function () {
    $promotion = ProductPromotion::factory()->create();
    $data = ProductPromotion::factory()->make(['product_id' => $promotion->product_id])->toArray();
    $response = test()->putJson("api/repo/product-promotions/{$promotion->id}", $data);
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'id', 'product_id', 'promotion_name', 'description',
            'validity_start', 'validity_end', 'booking_start', 'booking_end',
            'terms_conditions', 'exclusions', 'min_night_stay', 'max_night_stay',
        ],
        'message',
    ]);
    $this->assertDatabaseHas('pd_product_promotions', $data);
});

test('destroy', function () {
    $promotion = ProductPromotion::factory()->create();
    $response = test()->deleteJson("api/repo/product-promotions/{$promotion->id}");
    $response->assertStatus(204);
    $this->assertDatabaseMissing('pd_product_promotions', ['id' => $promotion->id]);
});

test('can attach gallery to product promotion', function () {
    $promotion = ProductPromotion::factory()->create();
    $gallery = ImageGallery::factory()->create();

    $response = test()->postJson("api/repo/product-promotions/{$promotion->id}/attach-gallery", [
        'gallery_id' => $gallery->id,
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'gallery_name', 'description'],
        ],
        'message',
    ]);
    $this->assertDatabaseHas('pd_product_promotion_gallery', [
        'product_promotion_id' => $promotion->id,
        'gallery_id' => $gallery->id,
    ]);
});

test('can detach gallery from product promotion', function () {
    $promotion = ProductPromotion::factory()->create();
    $gallery = ImageGallery::factory()->create();
    $promotion->galleries()->attach($gallery->id);

    $response = test()->postJson("api/repo/product-promotions/{$promotion->id}/detach-gallery", [
        'gallery_id' => $gallery->id,
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'message',
    ]);

    $this->assertDatabaseMissing('pd_product_promotion_gallery', [
        'product_promotion_id' => $promotion->id,
        'gallery_id' => $gallery->id,
    ]);
});