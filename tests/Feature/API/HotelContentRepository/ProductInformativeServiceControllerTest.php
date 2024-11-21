<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\ProductInformativeService;
use PHPUnit\Framework\Attributes\Test;

class ProductInformativeServiceControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_index()
    {
        ProductInformativeService::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/product-informative-services');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'product_id', 'service_id']
            ],
            'message'
        ]);
        $this->assertDatabaseCount('pd_product_informative_services', 3);
    }

    #[Test]
    public function test_store()
    {
        $data = ProductInformativeService::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/product-informative-services', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['id', 'product_id', 'service_id'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_product_informative_services', $data);
    }

    #[Test]
    public function test_show()
    {
        $service = ProductInformativeService::factory()->create();
        $response = $this->request()->getJson("api/repo/product-informative-services/{$service->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'product_id', 'service_id'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_product_informative_services', $service->toArray());
    }

    #[Test]
    public function test_update()
    {
        $service = ProductInformativeService::factory()->create();
        $data = ProductInformativeService::factory()->make(['product_id' => $service->product_id])->toArray();
        $response = $this->request()->putJson("api/repo/product-informative-services/{$service->id}", $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'product_id', 'service_id'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_product_informative_services', $data);
    }

    #[Test]
    public function test_destroy()
    {
        $service = ProductInformativeService::factory()->create();
        $response = $this->request()->deleteJson("api/repo/product-informative-services/{$service->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_product_informative_services', ['id' => $service->id]);
    }
}
