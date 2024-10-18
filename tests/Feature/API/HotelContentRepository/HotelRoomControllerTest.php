<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\HotelRoom;
use PHPUnit\Framework\Attributes\Test;

class HotelRoomControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_index()
    {
        HotelRoom::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/hotel-rooms');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'hotel_id', 'name', 'hbs_data_mapped_name', 'description'
                ]
            ],
            'message'
        ]);
        $this->assertDatabaseCount('pd_hotel_rooms', 3);
    }

    #[Test]
    public function test_store()
    {
        $data = HotelRoom::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/hotel-rooms', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id', 'hotel_id', 'name', 'hbs_data_mapped_name', 'description'
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_rooms', $data);
    }

    #[Test]
    public function test_show()
    {
        $hotelRoom = HotelRoom::factory()->create();
        $response = $this->request()->getJson("api/repo/hotel-rooms/{$hotelRoom->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id', 'hotel_id', 'name', 'hbs_data_mapped_name', 'description'
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_rooms', $hotelRoom->toArray());
    }

    #[Test]
    public function test_update()
    {
        $hotelRoom = HotelRoom::factory()->create();
        $data = HotelRoom::factory()->make(['hotel_id' => $hotelRoom->hotel_id])->toArray();
        $response = $this->request()->putJson("api/repo/hotel-rooms/{$hotelRoom->id}", $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id', 'hotel_id', 'name', 'hbs_data_mapped_name', 'description'
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_rooms', $data);
    }

    #[Test]
    public function test_destroy()
    {
        $hotelRoom = HotelRoom::factory()->create();
        $response = $this->request()->deleteJson("api/repo/hotel-rooms/{$hotelRoom->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_hotel_rooms', ['id' => $hotelRoom->id]);
    }
}
