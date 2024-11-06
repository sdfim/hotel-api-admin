<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\HotelWebFinderUnit;
use PHPUnit\Framework\Attributes\Test;

class HotelWebFinderUnitControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_index()
    {
        HotelWebFinderUnit::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/hotel-web-finder-units');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'field', 'value', 'web_finder_id']
            ],
            'message'
        ]);
        $this->assertDatabaseCount('pd_hotel_web_finder_units', 3);
    }

    #[Test]
    public function test_store()
    {
        $data = HotelWebFinderUnit::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/hotel-web-finder-units', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['id', 'field', 'value', 'web_finder_id'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_web_finder_units', $data);
    }

    #[Test]
    public function test_show()
    {
        $unit = HotelWebFinderUnit::factory()->create();
        $response = $this->request()->getJson("api/repo/hotel-web-finder-units/{$unit->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'field', 'value', 'web_finder_id'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_web_finder_units', $unit->toArray());
    }

    #[Test]
    public function test_update()
    {
        $unit = HotelWebFinderUnit::factory()->create();
        $data = HotelWebFinderUnit::factory()->make()->toArray();
        $response = $this->request()->putJson("api/repo/hotel-web-finder-units/{$unit->id}", $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'field', 'value', 'web_finder_id'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_web_finder_units', $data);
    }

    #[Test]
    public function test_destroy()
    {
        $unit = HotelWebFinderUnit::factory()->create();
        $response = $this->request()->deleteJson("api/repo/hotel-web-finder-units/{$unit->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_hotel_web_finder_units', ['id' => $unit->id]);
    }
}
