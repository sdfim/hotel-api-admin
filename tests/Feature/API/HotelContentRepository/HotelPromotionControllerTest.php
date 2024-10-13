<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\HotelPromotion;
use Modules\HotelContentRepository\Models\ImageGallery;
use PHPUnit\Framework\Attributes\Test;

class HotelPromotionControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_index()
    {
        HotelPromotion::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/hotel-promotions');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'hotel_id', 'promotion_name', 'description',
                    'validity_start', 'validity_end', 'booking_start', 'booking_end',
                    'terms_conditions', 'exclusions', 'deposit_info'
                ]
            ],
            'message'
        ]);
        $this->assertDatabaseCount('pd_hotel_promotions', 3);
    }

    #[Test]
    public function test_store()
    {
        $data = HotelPromotion::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/hotel-promotions', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id', 'hotel_id', 'promotion_name', 'description',
                'validity_start', 'validity_end', 'booking_start', 'booking_end',
                'terms_conditions', 'exclusions', 'deposit_info'
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_promotions', $data);
    }

    #[Test]
    public function test_show()
    {
        $promotion = HotelPromotion::factory()->create();
        $response = $this->request()->getJson("api/repo/hotel-promotions/{$promotion->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id', 'hotel_id', 'promotion_name', 'description',
                'validity_start', 'validity_end', 'booking_start', 'booking_end',
                'terms_conditions', 'exclusions', 'deposit_info'
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_promotions', $promotion->toArray());
    }

    #[Test]
    public function test_update()
    {
        $promotion = HotelPromotion::factory()->create();
        $data = HotelPromotion::factory()->make(['hotel_id' => $promotion->hotel_id])->toArray();
        $response = $this->request()->putJson("api/repo/hotel-promotions/{$promotion->id}", $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id', 'hotel_id', 'promotion_name', 'description',
                'validity_start', 'validity_end', 'booking_start', 'booking_end',
                'terms_conditions', 'exclusions', 'deposit_info'
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_promotions', $data);
    }

    #[Test]
    public function test_destroy()
    {
        $promotion = HotelPromotion::factory()->create();
        $response = $this->request()->deleteJson("api/repo/hotel-promotions/{$promotion->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_hotel_promotions', ['id' => $promotion->id]);
    }

    #[Test]
    public function test_can_attach_gallery_to_hotel_promotion()
    {
        $promotion = HotelPromotion::factory()->create();
        $gallery = ImageGallery::factory()->create();

        $response = $this->request()->postJson("api/repo/hotel-promotions/{$promotion->id}/attach-gallery", [
            'gallery_id' => $gallery->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'gallery_name', 'description', 'created_at']
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_promotion_gallery', [
            'hotel_promotion_id' => $promotion->id,
            'gallery_id' => $gallery->id,
        ]);
    }

    #[Test]
    public function test_can_detach_gallery_from_hotel_promotion()
    {
        $promotion = HotelPromotion::factory()->create();
        $gallery = ImageGallery::factory()->create();
        $promotion->galleries()->attach($gallery->id);

        $response = $this->request()->postJson("api/repo/hotel-promotions/{$promotion->id}/detach-gallery", [
            'gallery_id' => $gallery->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message'
        ]);

        $this->assertDatabaseMissing('pd_hotel_promotion_gallery', [
            'hotel_promotion_id' => $promotion->id,
            'gallery_id' => $gallery->id,
        ]);
    }
}
