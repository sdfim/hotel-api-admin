<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\ProductCancellationPolicy;
use PHPUnit\Framework\Attributes\Test;

class ProductCancellationPolicyControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_index()
    {
        ProductCancellationPolicy::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/product-cancellation-policy');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'product_id', 'name', 'start_date', 'expiration_date', 'manipulable_price_type', 'price_value', 'price_value_type', 'price_value_target'
                ]
            ],
            'message'
        ]);
        $this->assertDatabaseCount('pd_product_cancellation_policies', 3);
    }

    #[Test]
    public function test_store()
    {
        $data = ProductCancellationPolicy::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/product-cancellation-policy', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id', 'product_id', 'name', 'start_date', 'expiration_date', 'manipulable_price_type', 'price_value', 'price_value_type', 'price_value_target'
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_product_cancellation_policies', $data);
    }

    #[Test]
    public function test_show()
    {
        $cancellationPolicy = ProductCancellationPolicy::factory()->create();
        $response = $this->request()->getJson("api/repo/product-cancellation-policy/{$cancellationPolicy->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id', 'product_id', 'name', 'start_date', 'expiration_date', 'manipulable_price_type', 'price_value', 'price_value_type', 'price_value_target'
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_product_cancellation_policies', $cancellationPolicy->toArray());
    }

    #[Test]
    public function test_update()
    {
        $cancellationPolicy = ProductCancellationPolicy::factory()->create();
        $data = ProductCancellationPolicy::factory()->make(['product_id' => $cancellationPolicy->product_id])->toArray();
        $response = $this->request()->putJson("api/repo/product-cancellation-policy/{$cancellationPolicy->id}", $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id', 'product_id', 'name', 'start_date', 'expiration_date', 'manipulable_price_type', 'price_value', 'price_value_type', 'price_value_target'
            ],
            'message'
        ]);
        $this->assertDatabaseHas('pd_product_cancellation_policies', $data);
    }

    #[Test]
    public function test_destroy()
    {
        $cancellationPolicy = ProductCancellationPolicy::factory()->create();
        $response = $this->request()->deleteJson("api/repo/product-cancellation-policy/{$cancellationPolicy->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_product_cancellation_policies', ['id' => $cancellationPolicy->id]);
    }
}
