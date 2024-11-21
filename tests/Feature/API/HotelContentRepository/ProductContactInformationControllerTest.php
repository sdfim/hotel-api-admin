<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\ProductContactInformation;
use PHPUnit\Framework\Attributes\Test;

class ProductContactInformationControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_can_list_product_contact_informations()
    {
        ProductContactInformation::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/product-contact-information');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'product_id', 'first_name', 'last_name', 'email', 'phone']
            ],
            'message'
        ]);
    }

    #[Test]
    public function test_can_create_product_contact_information()
    {
        $data = ProductContactInformation::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/product-contact-information', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['id', 'product_id', 'first_name', 'last_name', 'email', 'phone'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_product_contact_information', $data);
    }

    #[Test]
    public function test_can_show_product_contact_information()
    {
        $contactInformation = ProductContactInformation::factory()->create();
        $response = $this->request()->getJson("api/repo/product-contact-information/{$contactInformation->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'product_id', 'first_name', 'last_name', 'email', 'phone'],
            'message'
        ]);
    }

    #[Test]
    public function test_can_update_product_contact_information()
    {
        $contactInformation = ProductContactInformation::factory()->create();
        $data = ProductContactInformation::factory()->make(['product_id' => $contactInformation->product_id])->toArray();
        $response = $this->request()->putJson("api/repo/product-contact-information/{$contactInformation->id}", $data);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'product_id', 'first_name', 'last_name', 'email', 'phone'],
                'message'
            ]);
        $this->assertDatabaseHas('pd_product_contact_information', $data);
    }

    #[Test]
    public function test_can_delete_product_contact_information()
    {
        $contactInformation = ProductContactInformation::factory()->create();
        $response = $this->request()->deleteJson("api/repo/product-contact-information/{$contactInformation->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_product_contact_information', ['id' => $contactInformation->id]);
    }
}
