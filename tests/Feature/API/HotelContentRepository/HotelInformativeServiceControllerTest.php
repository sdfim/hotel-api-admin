<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\HotelInformativeService;
use PHPUnit\Framework\Attributes\Test;

class HotelInformativeServiceControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_index()
    {
        HotelInformativeService::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/hotel-informative-services');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'hotel_id', 'service_name', 'service_description', 'service_cost']
            ],
            'message'
        ]);
        $this->assertDatabaseCount('pd_hotel_informative_services', 3);
    }

    #[Test]
    public function test_store()
    {
        $data = HotelInformativeService::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/hotel-informative-services', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['id', 'hotel_id', 'service_name', 'service_description', 'service_cost'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_informative_services', $data);
    }

    #[Test]
    public function test_show()
    {
        $service = HotelInformativeService::factory()->create();
        $response = $this->request()->getJson("api/repo/hotel-informative-services/{$service->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'hotel_id', 'service_name', 'service_description', 'service_cost'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_informative_services', $service->toArray());
    }

    #[Test]
    public function test_update()
    {
        $service = HotelInformativeService::factory()->create();
        $data = HotelInformativeService::factory()->make(['hotel_id' => $service->hotel_id])->toArray();
        $response = $this->request()->putJson("api/repo/hotel-informative-services/{$service->id}", $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'hotel_id', 'service_name', 'service_description', 'service_cost'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_informative_services', $data);
    }

    #[Test]
    public function test_destroy()
    {
        $service = HotelInformativeService::factory()->create();
        $response = $this->request()->deleteJson("api/repo/hotel-informative-services/{$service->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_hotel_informative_services', ['id' => $service->id]);
    }
}
