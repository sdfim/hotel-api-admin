<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\Image;
use PHPUnit\Framework\Attributes\Test;

class ImageControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_index()
    {
        Image::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/images');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id',  'image_url', 'tag', 'weight', 'section_id'],
            ],
            'message',
        ]);
        $this->assertDatabaseCount('pd_images', 3);
    }

    #[Test]
    public function test_store()
    {
        $data = Image::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/images', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['id',  'image_url', 'tag', 'weight', 'section_id'],
            'message',
        ]);
        $this->assertDatabaseHas('pd_images', $data);
    }

    #[Test]
    public function test_show()
    {
        $image = Image::factory()->create();
        $response = $this->request()->getJson("api/repo/images/{$image->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id',  'image_url', 'tag', 'weight', 'section_id'],
            'message',
        ]);
        $this->assertDatabaseHas('pd_images', $image->toArray());
    }

    #[Test]
    public function test_update()
    {
        $image = Image::factory()->create();
        $data = Image::factory()->make()->toArray();
        $response = $this->request()->putJson("api/repo/images/{$image->id}", $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id',  'image_url', 'tag', 'weight', 'section_id'],
            'message',
        ]);
        $this->assertDatabaseHas('pd_images', $data);
    }

    #[Test]
    public function test_destroy()
    {
        $image = Image::factory()->create();
        $response = $this->request()->deleteJson("api/repo/images/{$image->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_images', ['id' => $image->id]);
    }
}
