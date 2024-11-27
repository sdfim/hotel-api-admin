<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\ProductPromotion;
use Modules\HotelContentRepository\Models\ImageGallery;
use PHPUnit\Framework\Attributes\Test;

class ProductPromotionControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_index()
    {
        ProductPromotion::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/product-promotions');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'product_id', 'promotion_name', 'description',
                    'validity_start', 'validity_end', 'booking_start', 'booking_end',
                    'terms_conditions', 'exclusions'
                ]
            ],
            'message'
        ]);
        $this->assertDatabaseCount('pd_product_promotions', 3);
    }

    #[Test]
    public function test_store()
    {
        $data = ProductPromotion::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/product-promotions', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id', 'product_id', 'promotion_name', 'description',
                'validity_start', 'validity_end', 'booking_start', 'booking_end',
                'terms_conditions', 'exclusions'
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_product_promotions', $data);
    }

    #[Test]
    public function test_show()
    {
        $promotion = ProductPromotion::factory()->create();
        $response = $this->request()->getJson("api/repo/product-promotions/{$promotion->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id', 'product_id', 'promotion_name', 'description',
                'validity_start', 'validity_end', 'booking_start', 'booking_end',
                'terms_conditions', 'exclusions'
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_product_promotions', $promotion->toArray());
    }

    #[Test]
    public function test_update()
    {
        $promotion = ProductPromotion::factory()->create();
        $data = ProductPromotion::factory()->make(['product_id' => $promotion->product_id])->toArray();
        $response = $this->request()->putJson("api/repo/product-promotions/{$promotion->id}", $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id', 'product_id', 'promotion_name', 'description',
                'validity_start', 'validity_end', 'booking_start', 'booking_end',
                'terms_conditions', 'exclusions'
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_product_promotions', $data);
    }

    #[Test]
    public function test_destroy()
    {
        $promotion = ProductPromotion::factory()->create();
        $response = $this->request()->deleteJson("api/repo/product-promotions/{$promotion->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_product_promotions', ['id' => $promotion->id]);
    }

    #[Test]
    public function test_can_attach_gallery_to_product_promotion()
    {
        $promotion = ProductPromotion::factory()->create();
        $gallery = ImageGallery::factory()->create();

        $response = $this->request()->postJson("api/repo/product-promotions/{$promotion->id}/attach-gallery", [
            'gallery_id' => $gallery->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'gallery_name', 'description']
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_product_promotion_gallery', [
            'product_promotion_id' => $promotion->id,
            'gallery_id' => $gallery->id,
        ]);
    }

    #[Test]
    public function test_can_detach_gallery_from_product_promotion()
    {
        $promotion = ProductPromotion::factory()->create();
        $gallery = ImageGallery::factory()->create();
        $promotion->galleries()->attach($gallery->id);

        $response = $this->request()->postJson("api/repo/product-promotions/{$promotion->id}/detach-gallery", [
            'gallery_id' => $gallery->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message'
        ]);

        $this->assertDatabaseMissing('pd_product_promotion_gallery', [
            'product_promotion_id' => $promotion->id,
            'gallery_id' => $gallery->id,
        ]);
    }
}
