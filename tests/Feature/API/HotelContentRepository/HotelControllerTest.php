<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelWebFinder;
use Modules\HotelContentRepository\Models\ImageGallery;
use PHPUnit\Framework\Attributes\Test;

class HotelControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_can_list_hotels()
    {
        Hotel::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/hotels');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'name', 'weight', 'type', 'verified',
                    'address', 'star_rating', 'num_rooms', 'location',
                    'travel_agent_commission', 'hotel_board_basis',
                    'default_currency'
                ]
            ],
            'message'
        ]);
        $this->assertDatabaseCount('pd_hotels', 3);
    }

    #[Test]
    public function test_can_create_hotel()
    {
        $data = Hotel::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/hotels', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id', 'name', 'weight', 'type', 'verified',
                'address', 'star_rating', 'num_rooms', 'location',
                'travel_agent_commission', 'hotel_board_basis',
                'default_currency'
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotels', $data);
    }

    #[Test]
    public function test_can_show_hotel()
    {
        $hotel = Hotel::factory()->create();
        $response = $this->request()->getJson("api/repo/hotels/{$hotel->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id', 'name', 'weight', 'type', 'verified',
                    'address', 'star_rating', 'num_rooms', 'location',
                    'travel_agent_commission', 'hotel_board_basis', 'default_currency'
                ]
            ],
            'message'
            ]);
        $this->assertDatabaseHas('pd_hotels', $hotel->toArray());
    }

    #[Test]
    public function test_can_update_hotel()
    {
        $hotel = Hotel::factory()->create();
        $data = Hotel::factory()->make(['id' => $hotel->id])->toArray();
        $response = $this->request()->putJson("api/repo/hotels/{$hotel->id}", $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id', 'name', 'weight', 'type', 'verified',
                'address', 'star_rating', 'num_rooms', 'location',
                'travel_agent_commission', 'hotel_board_basis', 'default_currency'
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotels', $data);
    }

    #[Test]
    public function test_can_delete_hotel()
    {
        $hotel = Hotel::factory()->create();
        $response = $this->request()->deleteJson("api/repo/hotels/{$hotel->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_hotels', ['id' => $hotel->id]);
    }

    #[Test]
    public function test_can_attach_gallery_to_hotel()
    {
        $hotel = Hotel::factory()->create();
        $gallery = ImageGallery::factory()->create();

        $response = $this->request()->postJson("api/repo/hotels/{$hotel->id}/attach-gallery", [
            'gallery_id' => $gallery->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'gallery_name', 'description']
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_gallery', [
            'hotel_id' => $hotel->id,
            'gallery_id' => $gallery->id,
        ]);
    }

    #[Test]
    public function test_can_detach_gallery_from_hotel()
    {
        $hotel = Hotel::factory()->create();
        $gallery = ImageGallery::factory()->create();
        $hotel->galleries()->attach($gallery->id);

        $response = $this->request()->postJson("api/repo/hotels/{$hotel->id}/detach-gallery", [
            'gallery_id' => $gallery->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message'
        ]);

        $this->assertDatabaseMissing('pd_hotel_gallery', [
            'hotel_id' => $hotel->id,
            'gallery_id' => $gallery->id,
        ]);
    }

    #[Test]
    public function test_can_attach_web_finder_to_hotel()
    {
        $hotel = Hotel::factory()->create();
        $webFinder = HotelWebFinder::factory()->create();

        $response = $this->request()->postJson("api/repo/hotels/{$hotel->id}/attach-web-finder", [
            'web_finder_id' => $webFinder->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'base_url', 'finder', 'type', 'example']
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_web_finder_hotel', [
            'hotel_id' => $hotel->id,
            'web_finder_id' => $webFinder->id,
        ]);
    }

    #[Test]
    public function test_can_detach_web_finder_from_hotel()
    {
        $hotel = Hotel::factory()->create();
        $webFinder = HotelWebFinder::factory()->create();
        $hotel->webFinders()->attach($webFinder->id);

        $response = $this->request()->postJson("api/repo/hotels/{$hotel->id}/detach-web-finder", [
            'web_finder_id' => $webFinder->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message'
        ]);

        $this->assertDatabaseMissing('pd_hotel_web_finder_hotel', [
            'hotel_id' => $hotel->id,
            'web_finder_id' => $webFinder->id,
        ]);
    }
}
