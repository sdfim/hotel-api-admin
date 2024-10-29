<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelDescriptiveContent;
use PHPUnit\Framework\Attributes\Test;

class HotelDescriptiveContentControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_index()
    {
        HotelDescriptiveContent::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/hotel-descriptive-contents');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'content_sections_id',
                    'descriptive_type_id',
                    'value',
                ]
            ],
            'message'
        ]);
        $this->assertDatabaseCount('pd_hotel_descriptive_content', 3);
    }

    #[Test]
    public function test_store()
    {
        $data = HotelDescriptiveContent::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/hotel-descriptive-contents', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'content_sections_id',
                'descriptive_type_id',
                'value',
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_descriptive_content', $data);
    }

    #[Test]
    public function test_show()
    {
        $content = HotelDescriptiveContent::factory()->create();
        $response = $this->request()->getJson("api/repo/hotel-descriptive-contents/{$content->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'content_sections_id',
                'descriptive_type_id',
                'value',
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_descriptive_content', $content->toArray());
    }

    #[Test]
    public function test_update()
    {
        $content = HotelDescriptiveContent::factory()->create();
        $data = HotelDescriptiveContent::factory()->make()->toArray();
        $response = $this->request()->putJson("api/repo/hotel-descriptive-contents/{$content->id}", $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'content_sections_id',
                'descriptive_type_id',
                'value',
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_descriptive_content', $data);
    }

    #[Test]
    public function test_destroy()
    {
        $content = HotelDescriptiveContent::factory()->create();
        $response = $this->request()->deleteJson("api/repo/hotel-descriptive-contents/{$content->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_hotel_descriptive_content', ['id' => $content->id]);
    }
}
