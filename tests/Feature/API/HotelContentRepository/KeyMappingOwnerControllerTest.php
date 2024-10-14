<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\KeyMappingOwner;
use PHPUnit\Framework\Attributes\Test;

class KeyMappingOwnerControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_index()
    {
        KeyMappingOwner::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/key-mapping-owners');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name']
            ],
            'message'
        ]);
        $this->assertDatabaseCount('pd_key_mapping_owners', 3);
    }

    #[Test]
    public function test_store()
    {
        $data = KeyMappingOwner::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/key-mapping-owners', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['id', 'name'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_key_mapping_owners', $data);
    }

    #[Test]
    public function test_show()
    {
        $keyMappingOwner = KeyMappingOwner::factory()->create();
        $response = $this->request()->getJson("api/repo/key-mapping-owners/{$keyMappingOwner->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'name'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_key_mapping_owners', $keyMappingOwner->toArray());
    }

    #[Test]
    public function test_update()
    {
        $keyMappingOwner = KeyMappingOwner::factory()->create();
        $data = KeyMappingOwner::factory()->make()->toArray();
        $response = $this->request()->putJson("api/repo/key-mapping-owners/{$keyMappingOwner->id}", $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'name'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_key_mapping_owners', $data);
    }

    #[Test]
    public function test_destroy()
    {
        $keyMappingOwner = KeyMappingOwner::factory()->create();
        $response = $this->request()->deleteJson("api/repo/key-mapping-owners/{$keyMappingOwner->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_key_mapping_owners', ['id' => $keyMappingOwner->id]);
    }
}
