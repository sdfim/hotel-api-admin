<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\HotelWebFinder;
use PHPUnit\Framework\Attributes\Test;

class HotelWebFinderControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_index()
    {
        HotelWebFinder::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/hotel-web-finders');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'base_url', 'finder', 'website', 'example'],
            ],
            'message',
        ]);
        $this->assertDatabaseCount('pd_hotel_web_finders', 3);
    }

    #[Test]
    public function test_store()
    {
        $data = HotelWebFinder::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/hotel-web-finders', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['id', 'base_url', 'finder', 'website', 'example'],
            'message',
        ]);
        $this->assertDatabaseHas('pd_hotel_web_finders', $data);
    }

    #[Test]
    public function test_show()
    {
        $webFinder = HotelWebFinder::factory()->create();
        $response = $this->request()->getJson("api/repo/hotel-web-finders/{$webFinder->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'base_url', 'finder', 'website', 'example'],
            'message',
        ]);
        $this->assertDatabaseHas('pd_hotel_web_finders', $webFinder->toArray());
    }

    #[Test]
    public function test_update()
    {
        $webFinder = HotelWebFinder::factory()->create();
        $data = HotelWebFinder::factory()->make()->toArray();
        $response = $this->request()->putJson("api/repo/hotel-web-finders/{$webFinder->id}", $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'base_url', 'finder', 'website', 'example'],
            'message',
        ]);
        $this->assertDatabaseHas('pd_hotel_web_finders', $data);
    }

    #[Test]
    public function test_destroy()
    {
        $webFinder = HotelWebFinder::factory()->create();
        $response = $this->request()->deleteJson("api/repo/hotel-web-finders/{$webFinder->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_hotel_web_finders', ['id' => $webFinder->id]);
    }
}
