<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\Vendor;
use PHPUnit\Framework\Attributes\Test;

class VendorControllerTest extends TestCase
{
    #[Test]
    public function test_can_list_vendors()
    {
        $initialCount = Vendor::count();
        Vendor::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/vendors');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'name', 'address', 'lat', 'lng', 'website'
                ]
            ],
            'message'
        ]);
        $this->assertDatabaseCount('pd_vendors', $initialCount + 3);
    }

    #[Test]
    public function test_can_create_vendor()
    {
        $data = Vendor::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/vendors', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id', 'name', 'address', 'lat', 'lng', 'website'
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_vendors', $data);
    }

    #[Test]
    public function test_can_show_vendor()
    {
        $vendor = Vendor::factory()->create();
        $response = $this->request()->getJson("api/repo/vendors/{$vendor->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id', 'name', 'address', 'lat', 'lng', 'website'
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_vendors', $vendor->toArray());
    }

    #[Test]
    public function test_can_update_vendor()
    {
        $vendor = Vendor::factory()->create();
        $data = Vendor::factory()->make(['id' => $vendor->id])->toArray();
        $response = $this->request()->putJson("api/repo/vendors/{$vendor->id}", $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id', 'name', 'address', 'lat', 'lng', 'website'
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_vendors', $data);
    }

    #[Test]
    public function test_can_delete_vendor()
    {
        $vendor = Vendor::factory()->create();
        $response = $this->request()->deleteJson("api/repo/vendors/{$vendor->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_vendors', ['id' => $vendor->id]);
    }
}
