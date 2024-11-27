<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\ProductDescriptiveContent;
use PHPUnit\Framework\Attributes\Test;

class ProductDescriptiveContentControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_index()
    {
        ProductDescriptiveContent::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/product-descriptive-contents');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'content_sections_id',
                    'descriptive_type_id',
                    'value',
                ]
            ],
            'message'
        ]);
        $this->assertDatabaseCount('pd_product_descriptive_content', 3);
    }

    #[Test]
    public function test_store()
    {
        $data = ProductDescriptiveContent::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/product-descriptive-contents', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'content_sections_id',
                'descriptive_type_id',
                'value',
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_product_descriptive_content', $data);
    }

    #[Test]
    public function test_show()
    {
        $content = ProductDescriptiveContent::factory()->create();
        $response = $this->request()->getJson("api/repo/product-descriptive-contents/{$content->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'content_sections_id',
                'descriptive_type_id',
                'value',
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_product_descriptive_content', $content->toArray());
    }

    #[Test]
    public function test_update()
    {
        $content = ProductDescriptiveContent::factory()->create();
        $data = ProductDescriptiveContent::factory()->make()->toArray();
        $response = $this->request()->putJson("api/repo/product-descriptive-contents/{$content->id}", $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'content_sections_id',
                'descriptive_type_id',
                'value',
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_product_descriptive_content', $data);
    }

    #[Test]
    public function test_destroy()
    {
        $content = ProductDescriptiveContent::factory()->create();
        $response = $this->request()->deleteJson("api/repo/product-descriptive-contents/{$content->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_product_descriptive_content', ['id' => $content->id]);
    }
}
