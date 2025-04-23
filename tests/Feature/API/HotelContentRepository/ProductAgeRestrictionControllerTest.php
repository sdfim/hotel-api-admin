<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\ProductAgeRestriction;
use PHPUnit\Framework\Attributes\Test;

class ProductAgeRestrictionControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_can_create_product_age_restriction()
    {
        $data = ProductAgeRestriction::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/age-restrictions', $data);
        $response->assertStatus(201)
            ->assertJsonFragment($data);
        $this->assertDatabaseHas('pd_product_age_restrictions', $data);
    }

    #[Test]
    public function test_can_update_product_age_restriction()
    {
        $ageRestriction = ProductAgeRestriction::factory()->create();
        $data = ProductAgeRestriction::factory()->make([
            'product_id' => $ageRestriction->product_id,
            'restriction_type' => $ageRestriction->restriction_type,
        ])->toArray();

        $response = $this->request()->putJson("api/repo/age-restrictions/{$ageRestriction->id}", $data);
        $response->assertStatus(200)
            ->assertJsonFragment($data);
        $this->assertDatabaseHas('pd_product_age_restrictions', $data);
    }

    #[Test]
    public function test_can_delete_product_age_restriction()
    {
        $ageRestriction = ProductAgeRestriction::factory()->create();
        $response = $this->request()->deleteJson("api/repo/age-restrictions/{$ageRestriction->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_product_age_restrictions', ['id' => $ageRestriction->id]);
    }

    #[Test]
    public function test_can_list_product_age_restrictions()
    {
        $ageRestrictions = ProductAgeRestriction::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/age-restrictions');
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function test_can_show_product_age_restriction()
    {
        $ageRestriction = ProductAgeRestriction::factory()->create();
        $response = $this->request()->getJson("api/repo/age-restrictions/{$ageRestriction->id}");
        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $ageRestriction->id,
                'product_id' => $ageRestriction->product_id,
                'restriction_type' => $ageRestriction->restriction_type,
            ]);
    }
}
