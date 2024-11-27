<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\ProductAffiliation;
use PHPUnit\Framework\Attributes\Test;

class ProductAffiliationControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_can_create_product_affiliation()
    {
        $data = ProductAffiliation::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/product-affiliations', $data);
        $response->assertStatus(201)
            ->assertJsonFragment($data);
        $this->assertDatabaseHas('pd_product_affiliations', $data);
    }

    #[Test]
    public function test_can_update_product_affiliation()
    {
        $productAffiliation = ProductAffiliation::factory()->create();
        $data = ProductAffiliation::factory()->make(['product_id' => $productAffiliation->product_id])->toArray();
        $response = $this->request()->putJson("api/repo/product-affiliations/{$productAffiliation->id}", $data);
        $response->assertStatus(200)
            ->assertJsonFragment($data);
        $this->assertDatabaseHas('pd_product_affiliations', $data);
    }

    #[Test]
    public function test_can_delete_product_affiliation()
    {
        $productAffiliation = ProductAffiliation::factory()->create();
        $response = $this->request()->deleteJson("api/repo/product-affiliations/{$productAffiliation->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_product_affiliations', ['id' => $productAffiliation->id]);
    }

    #[Test]
    public function test_can_list_pd_product_affiliations()
    {
        $productAffiliations = ProductAffiliation::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/product-affiliations');
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function test_can_show_product_affiliation()
    {
        $productAffiliation = ProductAffiliation::factory()->create();
        $response = $this->request()->getJson("api/repo/product-affiliations/{$productAffiliation->id}");
        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $productAffiliation->id,
                'affiliation_name' => $productAffiliation->affiliation_name,
            ]);
    }
}
