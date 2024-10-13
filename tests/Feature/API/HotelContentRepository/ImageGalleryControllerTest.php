<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\HotelImage;
use Modules\HotelContentRepository\Models\ImageGallery;
use PHPUnit\Framework\Attributes\Test;

class ImageGalleryControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_index()
    {
        ImageGallery::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/image-galleries');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'gallery_name', 'description']
            ],
            'message'
        ]);
        $this->assertDatabaseCount('pd_image_galleries', 3);
    }

    #[Test]
    public function test_store()
    {
        $data = ImageGallery::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/image-galleries', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['id', 'gallery_name', 'description'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_image_galleries', $data);
    }

    #[Test]
    public function test_show()
    {
        $gallery = ImageGallery::factory()->create();
        $response = $this->request()->getJson("api/repo/image-galleries/{$gallery->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'gallery_name', 'description'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_image_galleries', $gallery->toArray());
    }

    #[Test]
    public function test_update()
    {
        $gallery = ImageGallery::factory()->create();
        $data = ImageGallery::factory()->make()->toArray();
        $response = $this->request()->putJson("api/repo/image-galleries/{$gallery->id}", $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'gallery_name', 'description'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_image_galleries', $data);
    }

    #[Test]
    public function test_destroy()
    {
        $gallery = ImageGallery::factory()->create();
        $response = $this->request()->deleteJson("api/repo/image-galleries/{$gallery->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_image_galleries', ['id' => $gallery->id]);
    }

    #[Test]
    public function test_attach_image()
    {
        $gallery = ImageGallery::factory()->create();
        $image = HotelImage::factory()->create();

        $response = $this->request()->postJson("api/repo/image-galleries/{$gallery->id}/attach-image", [
            'image_id' => $image->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id', 'gallery_name', 'description', 'images' => [
                    '*' => ['id', 'image_url', 'tag', 'weight', 'section']
                ]
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_gallery_images', [
            'gallery_id' => $gallery->id,
            'image_id' => $image->id,
        ]);
    }

    #[Test]
    public function test_detach_image()
    {
        $gallery = ImageGallery::factory()->create();
        $image = HotelImage::factory()->create(); // Assuming you have an Image factory
        $gallery->images()->attach($image->id);

        $response = $this->request()->postJson("api/repo/image-galleries/{$gallery->id}/detach-image", [
            'image_id' => $image->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id', 'gallery_name', 'description', 'images' => []
            ],
            'message'
        ]);
        $this->assertDatabaseMissing('pd_gallery_images', [
            'gallery_id' => $gallery->id,
            'image_id' => $image->id,
        ]);
    }
}
