<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\HotelImageSection;
use PHPUnit\Framework\Attributes\Test;

class HotelImageSectionControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_index()
    {
        HotelImageSection::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/hotel-image-sections');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name']
            ],
            'message'
        ]);
        $this->assertDatabaseCount('pd_hotel_image_sections', 3);
    }

    #[Test]
    public function test_store()
    {
        $data = HotelImageSection::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/hotel-image-sections', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['id', 'name'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_image_sections', $data);
    }

    #[Test]
    public function test_show()
    {
        $hotelImageSection = HotelImageSection::factory()->create();
        $response = $this->request()->getJson("api/repo/hotel-image-sections/{$hotelImageSection->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'name'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_image_sections', $hotelImageSection->toArray());
    }

    #[Test]
    public function test_update()
    {
        $hotelImageSection = HotelImageSection::factory()->create();
        $data = HotelImageSection::factory()->make()->toArray();
        $response = $this->request()->putJson("api/repo/hotel-image-sections/{$hotelImageSection->id}", $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'name'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_image_sections', $data);
    }

    #[Test]
    public function test_destroy()
    {
        $hotelImageSection = HotelImageSection::factory()->create();
        $response = $this->request()->deleteJson("api/repo/hotel-image-sections/{$hotelImageSection->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_hotel_image_sections', ['id' => $hotelImageSection->id]);
    }
}
