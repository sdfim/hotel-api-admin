<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\KeyMapping;
use PHPUnit\Framework\Attributes\Test;

class KeyMappingControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_index()
    {
        KeyMapping::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/key-mappings');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'product_id', 'key_id', 'key_mapping_owner_id']
            ],
            'message'
        ]);
        $this->assertDatabaseCount('pd_key_mapping', 3);
    }

    #[Test]
    public function test_store()
    {
        $data = KeyMapping::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/key-mappings', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['id', 'product_id', 'key_id', 'key_mapping_owner_id'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_key_mapping', $data);
    }

    #[Test]
    public function test_show()
    {
        $keyMapping = KeyMapping::factory()->create();
        $response = $this->request()->getJson("api/repo/key-mappings/{$keyMapping->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'product_id', 'key_id', 'key_mapping_owner_id'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_key_mapping', $keyMapping->toArray());
    }

    #[Test]
    public function test_update()
    {
        $keyMapping = KeyMapping::factory()->create();
        $data = KeyMapping::factory()->make()->toArray();
        $response = $this->request()->putJson("api/repo/key-mappings/{$keyMapping->id}", $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'product_id', 'key_id', 'key_mapping_owner_id'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_key_mapping', $data);
    }

    #[Test]
    public function test_destroy()
    {
        $keyMapping = KeyMapping::factory()->create();
        $response = $this->request()->deleteJson("api/repo/key-mappings/{$keyMapping->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_key_mapping', ['id' => $keyMapping->id]);
    }
}
