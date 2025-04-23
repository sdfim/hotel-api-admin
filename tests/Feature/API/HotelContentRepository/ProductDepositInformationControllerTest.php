<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\ProductDepositInformation;
use PHPUnit\Framework\Attributes\Test;

class ProductDepositInformationControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_index()
    {
        ProductDepositInformation::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/product-deposit-information');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'product_id', 'name', 'start_date', 'expiration_date', 'manipulable_price_type', 'price_value', 'price_value_type', 'price_value_target',
                ],
            ],
            'message',
        ]);
        $this->assertDatabaseCount('pd_product_deposit_information', 3);
    }

    #[Test]
    public function test_store()
    {
        $data = ProductDepositInformation::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/product-deposit-information', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id', 'product_id', 'name', 'start_date', 'expiration_date', 'manipulable_price_type', 'price_value', 'price_value_type', 'price_value_target',
            ],
            'message',
        ]);
        $this->assertDatabaseHas('pd_product_deposit_information', $data);
    }

    #[Test]
    public function test_show()
    {
        $depositInformation = ProductDepositInformation::factory()->create();
        $response = $this->request()->getJson("api/repo/product-deposit-information/{$depositInformation->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id', 'product_id', 'name', 'start_date', 'expiration_date', 'manipulable_price_type', 'price_value', 'price_value_type', 'price_value_target',
            ],
            'message',
        ]);
        $this->assertDatabaseHas('pd_product_deposit_information', $depositInformation->toArray());
    }

    #[Test]
    public function test_update()
    {
        $depositInformation = ProductDepositInformation::factory()->create();
        $data = ProductDepositInformation::factory()->make(['product_id' => $depositInformation->product_id])->toArray();
        $response = $this->request()->putJson("api/repo/product-deposit-information/{$depositInformation->id}", $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id', 'product_id', 'name', 'start_date', 'expiration_date', 'manipulable_price_type', 'price_value', 'price_value_type', 'price_value_target',
            ],
            'message',
        ]);
        $this->assertDatabaseHas('pd_product_deposit_information', $data);
    }

    #[Test]
    public function test_destroy()
    {
        $depositInformation = ProductDepositInformation::factory()->create();
        $response = $this->request()->deleteJson("api/repo/product-deposit-information/{$depositInformation->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_product_deposit_information', ['id' => $depositInformation->id]);
    }
}
