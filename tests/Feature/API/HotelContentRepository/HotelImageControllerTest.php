<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\HotelImage;
use PHPUnit\Framework\Attributes\Test;

class HotelImageControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_index()
    {
        HotelImage::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/hotel-images');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id',  'image_url', 'tag', 'weight', 'section_id']
            ],
            'message'
        ]);
        $this->assertDatabaseCount('pd_hotel_images', 3);
    }

    #[Test]
    public function test_store()
    {
        $data = HotelImage::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/hotel-images', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['id',  'image_url', 'tag', 'weight', 'section_id'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_images', $data);
    }

    #[Test]
    public function test_show()
    {
        $image = HotelImage::factory()->create();
        $response = $this->request()->getJson("api/repo/hotel-images/{$image->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id',  'image_url', 'tag', 'weight', 'section_id'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_images', $image->toArray());
    }

    #[Test]
    public function test_update()
    {
        $image = HotelImage::factory()->create();
        $data = HotelImage::factory()->make()->toArray();
        $response = $this->request()->putJson("api/repo/hotel-images/{$image->id}", $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id',  'image_url', 'tag', 'weight', 'section_id'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_images', $data);
    }

    #[Test]
    public function test_destroy()
    {
        $image = HotelImage::factory()->create();
        $response = $this->request()->deleteJson("api/repo/hotel-images/{$image->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_hotel_images', ['id' => $image->id]);
    }
}
