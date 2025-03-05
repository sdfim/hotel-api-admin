<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\ImageSection;
use PHPUnit\Framework\Attributes\Test;

class ImageSectionControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_index()
    {
        ImageSection::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/image-sections');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name'],
            ],
            'message',
        ]);
        $this->assertDatabaseCount('pd_image_sections', 3);
    }

    #[Test]
    public function test_store()
    {
        $data = ImageSection::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/image-sections', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['id', 'name'],
            'message',
        ]);
        $this->assertDatabaseHas('pd_image_sections', $data);
    }

    #[Test]
    public function test_show()
    {
        $hotelImageSection = ImageSection::factory()->create();
        $response = $this->request()->getJson("api/repo/image-sections/{$hotelImageSection->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'name'],
            'message',
        ]);
        $this->assertDatabaseHas('pd_image_sections', $hotelImageSection->toArray());
    }

    #[Test]
    public function test_update()
    {
        $hotelImageSection = ImageSection::factory()->create();
        $data = ImageSection::factory()->make()->toArray();
        $response = $this->request()->putJson("api/repo/image-sections/{$hotelImageSection->id}", $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'name'],
            'message',
        ]);
        $this->assertDatabaseHas('pd_image_sections', $data);
    }

    #[Test]
    public function test_destroy()
    {
        $hotelImageSection = ImageSection::factory()->create();
        $response = $this->request()->deleteJson("api/repo/image-sections/{$hotelImageSection->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_image_sections', ['id' => $hotelImageSection->id]);
    }
}
