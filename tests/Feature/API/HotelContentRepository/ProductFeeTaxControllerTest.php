<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\ProductFeeTax;
use PHPUnit\Framework\Attributes\Test;

class ProductFeeTaxControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_index()
    {
        ProductFeeTax::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/product-fee-taxes');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'product_id', 'name', 'net_value', 'rack_value', 'value_type',  'commissionable',  'collected_by', 'type'],
            ],
            'message',
        ]);
        $this->assertDatabaseCount('pd_product_fees_and_taxes', 3);
    }

    #[Test]
    public function test_store()
    {
        $data = ProductFeeTax::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/product-fee-taxes', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['id', 'product_id', 'name', 'net_value', 'rack_value', 'value_type',  'commissionable',  'collected_by', 'type'],
            'message',
        ]);
        $this->assertDatabaseHas('pd_product_fees_and_taxes', $data);
    }

    #[Test]
    public function test_show()
    {
        $feeTax = ProductFeeTax::factory()->create();
        $response = $this->request()->getJson("api/repo/product-fee-taxes/{$feeTax->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'product_id', 'name', 'net_value', 'rack_value', 'value_type',  'commissionable',  'collected_by', 'type'],
            'message',
        ]);
        $this->assertDatabaseHas('pd_product_fees_and_taxes', $feeTax->toArray());
    }

    #[Test]
    public function test_update()
    {
        $feeTax = ProductFeeTax::factory()->create();
        $data = ProductFeeTax::factory()->make()->toArray();
        $response = $this->request()->putJson('api/repo/product-fee-taxes/'.$feeTax->id, $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'product_id', 'name', 'net_value', 'rack_value', 'value_type',  'commissionable',  'collected_by', 'type'],
            'message',
        ]);
        $this->assertDatabaseHas('pd_product_fees_and_taxes', $data);
    }

    #[Test]
    public function test_destroy()
    {
        $feeTax = ProductFeeTax::factory()->create();
        $response = $this->request()->deleteJson("api/repo/product-fee-taxes/{$feeTax->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_product_fees_and_taxes', ['id' => $feeTax->id]);
    }
}
