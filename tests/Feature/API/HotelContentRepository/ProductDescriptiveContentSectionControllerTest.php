<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\ProductDescriptiveContent;
use Modules\HotelContentRepository\Models\ProductDescriptiveContentSection;
use PHPUnit\Framework\Attributes\Test;

class ProductDescriptiveContentSectionControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_index()
    {
        ProductDescriptiveContentSection::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/product-descriptive-content-sections');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'product_id', 'section_name', 'start_date', 'end_date']
            ],
            'message'
        ]);
        $this->assertDatabaseCount('pd_product_descriptive_content_sections', 3);
    }

    #[Test]
    public function test_store()
    {
        $data = ProductDescriptiveContentSection::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/product-descriptive-content-sections', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['id', 'product_id', 'section_name', 'start_date', 'end_date'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_product_descriptive_content_sections', $data);
    }

    #[Test]
    public function test_show()
    {
        $content = ProductDescriptiveContentSection::factory()->create();
        $response = $this->request()->getJson("api/repo/product-descriptive-content-sections/{$content->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'product_id', 'section_name', 'start_date', 'end_date'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_product_descriptive_content_sections', $content->toArray());
    }

    #[Test]
    public function test_update()
    {
        $content = ProductDescriptiveContentSection::factory()->create();
        $data = ProductDescriptiveContentSection::factory()->make()->toArray();
        $response = $this->request()->putJson("api/repo/product-descriptive-content-sections/{$content->id}", $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'product_id', 'section_name', 'start_date', 'end_date'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_product_descriptive_content_sections', $data);
    }

    #[Test]
    public function test_destroy()
    {
        $content = ProductDescriptiveContentSection::factory()->create();
        $response = $this->request()->deleteJson("api/repo/product-descriptive-content-sections/{$content->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_product_descriptive_content_sections', ['id' => $content->id]);
    }
}
