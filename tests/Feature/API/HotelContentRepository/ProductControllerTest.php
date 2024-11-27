<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\ImageGallery;
use Modules\HotelContentRepository\Models\Product;
use PHPUnit\Framework\Attributes\Test;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_can_list_products()
    {
        Product::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/products');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'vendor_id', 'product_type', 'name', 'verified',
                    'content_source_id', 'property_images_source_id', 'default_currency',
                    'website', 'lat', 'lng', 'related_id', 'related_type'
                ]
            ],
            'message'
        ]);
        $this->assertDatabaseCount('pd_products', 3);
    }

    #[Test]
    public function test_can_create_product()
    {
        $data = Product::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/products', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id', 'vendor_id', 'product_type', 'name', 'verified',
                'content_source_id', 'property_images_source_id', 'default_currency',
                'website', 'lat', 'lng', 'related_id', 'related_type'
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_products', $data);
    }

    #[Test]
    public function test_can_show_product()
    {
        $product = Product::factory()->create();
        $response = $this->request()->getJson("api/repo/products/{$product->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'vendor_id', 'product_type', 'name', 'verified',
                    'content_source_id', 'property_images_source_id', 'default_currency',
                    'website', 'lat', 'lng', 'related_id', 'related_type'
                ]
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_products', $product->toArray());
    }

    #[Test]
    public function test_can_update_product()
    {
        $product = Product::factory()->create();
        $data = Product::factory()->make(['id' => $product->id])->toArray();
        $response = $this->request()->putJson("api/repo/products/{$product->id}", $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id', 'vendor_id', 'product_type', 'name', 'verified',
                'content_source_id', 'property_images_source_id', 'default_currency',
                'website', 'lat', 'lng', 'related_id', 'related_type'
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_products', $data);
    }

    #[Test]
    public function test_can_delete_product()
    {
        $product = Product::factory()->create();
        $response = $this->request()->deleteJson("api/repo/products/{$product->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_products', ['id' => $product->id]);
    }

    #[Test]
    public function test_can_attach_gallery_to_hotel()
    {
        $product = Product::factory()->create();
        $gallery = ImageGallery::factory()->create();

        $response = $this->request()->postJson("api/repo/products/{$product->id}/attach-gallery", [
            'gallery_id' => $gallery->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'gallery_name', 'description']
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_product_gallery', [
            'product_id' => $product->id,
            'gallery_id' => $gallery->id,
        ]);
    }

    #[Test]
    public function test_can_detach_gallery_from_hotel()
    {
        $product = Product::factory()->create();
        $gallery = ImageGallery::factory()->create();
        $product->galleries()->attach($gallery->id);

        $response = $this->request()->postJson("api/repo/products/{$product->id}/detach-gallery", [
            'gallery_id' => $gallery->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message'
        ]);

        $this->assertDatabaseMissing('pd_product_gallery', [
            'product_id' => $product->id,
            'gallery_id' => $gallery->id,
        ]);
    }
}
