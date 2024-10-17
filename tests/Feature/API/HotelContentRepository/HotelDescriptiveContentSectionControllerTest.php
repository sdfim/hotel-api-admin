<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelDescriptiveContent;
use Modules\HotelContentRepository\Models\HotelDescriptiveContentSection;
use PHPUnit\Framework\Attributes\Test;

class HotelDescriptiveContentSectionControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_index()
    {
        HotelDescriptiveContentSection::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/hotel-descriptive-content-sections');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'hotel_id', 'section_name', 'start_date', 'end_date']
            ],
            'message'
        ]);
        $this->assertDatabaseCount('pd_hotel_descriptive_content_sections', 3);
    }

    #[Test]
    public function test_store()
    {
        $data = HotelDescriptiveContentSection::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/hotel-descriptive-content-sections', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['id', 'hotel_id', 'section_name', 'start_date', 'end_date'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_descriptive_content_sections', $data);
    }

    #[Test]
    public function test_show()
    {
        $content = HotelDescriptiveContentSection::factory()->create();
        $response = $this->request()->getJson("api/repo/hotel-descriptive-content-sections/{$content->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'hotel_id', 'section_name', 'start_date', 'end_date'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_descriptive_content_sections', $content->toArray());
    }

    #[Test]
    public function test_update()
    {
        $content = HotelDescriptiveContentSection::factory()->create();
        $data = HotelDescriptiveContentSection::factory()->make()->toArray();
        $response = $this->request()->putJson("api/repo/hotel-descriptive-content-sections/{$content->id}", $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'hotel_id', 'section_name', 'start_date', 'end_date'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_descriptive_content_sections', $data);
    }

    #[Test]
    public function test_destroy()
    {
        $content = HotelDescriptiveContentSection::factory()->create();
        $response = $this->request()->deleteJson("api/repo/hotel-descriptive-content-sections/{$content->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_hotel_descriptive_content_sections', ['id' => $content->id]);
    }
}
