<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\ProductAttribute;
use PHPUnit\Framework\Attributes\Test;

class ProductAttributeControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_can_list_product_attributes()
    {
        ProductAttribute::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/product-attributes');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'product_id', 'config_attribute_id'],
            ],
            'message',
        ]);
    }

    #[Test]
    public function test_can_create_product_attribute()
    {
        $data = ProductAttribute::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/product-attributes', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['id', 'product_id', 'config_attribute_id'],
            'message',
        ]);
        $this->assertDatabaseHas('pd_product_attributes', $data);
    }

    #[Test]
    public function test_can_show_product_attribute()
    {
        $attribute = ProductAttribute::factory()->create();
        $response = $this->request()->getJson("api/repo/product-attributes/{$attribute->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'product_id', 'config_attribute_id'],
            'message',
        ]);
    }

    #[Test]
    public function test_can_update_product_attribute()
    {
        $attribute = ProductAttribute::factory()->create();
        $data = ProductAttribute::factory()->make(['product_id' => $attribute->product_id])->toArray();
        $response = $this->request()->putJson("api/repo/product-attributes/{$attribute->id}", $data);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'product_id', 'config_attribute_id'],
                'message',
            ]);
        $this->assertDatabaseHas('pd_product_attributes', $data);
    }

    #[Test]
    public function test_can_delete_product_attribute()
    {
        $attribute = ProductAttribute::factory()->create();
        $response = $this->request()->deleteJson("api/repo/product-attributes/{$attribute->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_product_attributes', ['id' => $attribute->id]);
    }
}
