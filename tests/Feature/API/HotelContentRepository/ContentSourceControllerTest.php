<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\ContentSource;
use PHPUnit\Framework\Attributes\Test;

class ContentSourceControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_index()
    {
        ContentSource::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/content-sources');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name']
            ],
            'message'
        ]);
        $this->assertDatabaseCount('pd_content_sources', 3);
    }

    #[Test]
    public function test_store()
    {
        $data = ContentSource::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/content-sources', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['id', 'name'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_content_sources', $data);
    }

    #[Test]
    public function test_show()
    {
        $contentSource = ContentSource::factory()->create();
        $response = $this->request()->getJson("api/repo/content-sources/{$contentSource->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'name'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_content_sources', $contentSource->toArray());
    }

    #[Test]
    public function test_update()
    {
        $contentSource = ContentSource::factory()->create();
        $data = ContentSource::factory()->make()->toArray();
        $response = $this->request()->putJson("api/repo/content-sources/{$contentSource->id}", $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'name'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_content_sources', $data);
    }

    #[Test]
    public function test_destroy()
    {
        $contentSource = ContentSource::factory()->create();
        $response = $this->request()->deleteJson("api/repo/content-sources/{$contentSource->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_content_sources', ['id' => $contentSource->id]);
    }
}
