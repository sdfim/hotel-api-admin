<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelAttribute;
use PHPUnit\Framework\Attributes\Test;

class HotelAttributeControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_can_list_hotel_attributes()
    {
        HotelAttribute::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/hotel-attributes');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'hotel_id', 'hotel_id']
            ],
            'message'
        ]);
    }

    #[Test]
    public function test_can_create_hotel_attribute()
    {
        $data = HotelAttribute::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/hotel-attributes', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['id', 'hotel_id', 'hotel_id'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_attributes', $data);
    }

    #[Test]
    public function test_can_show_hotel_attribute()
    {
        $attribute = HotelAttribute::factory()->create();
        $response = $this->request()->getJson("api/repo/hotel-attributes/{$attribute->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'hotel_id', 'hotel_id'],
            'message'
        ]);
    }

    #[Test]
    public function test_can_update_hotel_attribute()
    {
        $attribute = HotelAttribute::factory()->create();
        $data = HotelAttribute::factory()->make(['hotel_id' => $attribute->hotel_id])->toArray();
        $response = $this->request()->putJson("api/repo/hotel-attributes/{$attribute->id}", $data);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'hotel_id', 'hotel_id'],
                'message'
            ]);
        $this->assertDatabaseHas('pd_hotel_attributes', $data);
    }

    #[Test]
    public function test_can_delete_hotel_attribute()
    {
        $attribute = HotelAttribute::factory()->create();
        $response = $this->request()->deleteJson("api/repo/hotel-attributes/{$attribute->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_hotel_attributes', ['id' => $attribute->id]);
    }
}
