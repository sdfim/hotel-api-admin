<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\HotelAgeRestriction;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelAgeRestrictionType;
use PHPUnit\Framework\Attributes\Test;

class HotelAgeRestrictionControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_can_create_hotel_age_restriction()
    {
        $data = HotelAgeRestriction::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/age-restrictions', $data);
        $response->assertStatus(201)
            ->assertJsonFragment($data);
        $this->assertDatabaseHas('pd_hotel_age_restrictions', $data);
    }

    #[Test]
    public function test_can_update_hotel_age_restriction()
    {
        $restriction = HotelAgeRestriction::factory()->make();
        $hotelAgeRestriction = HotelAgeRestriction::factory()->create();
        $data = HotelAgeRestriction::factory()->make([
            'hotel_id' => $hotelAgeRestriction->hotel_id,
            'restriction_type_id' => $hotelAgeRestriction->restriction_type_id,
        ])->toArray();

        $response = $this->request()->putJson("api/repo/age-restrictions/{$hotelAgeRestriction->id}", $data);
        $response->assertStatus(200)
            ->assertJsonFragment($data);
        $this->assertDatabaseHas('pd_hotel_age_restrictions', $data);
    }

    #[Test]
    public function test_can_delete_hotel_age_restriction()
    {
        $hotelAgeRestriction = HotelAgeRestriction::factory()->create();
        $response = $this->request()->deleteJson("api/repo/age-restrictions/{$hotelAgeRestriction->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_hotel_age_restrictions', ['id' => $hotelAgeRestriction->id]);
    }

    #[Test]
    public function test_can_list_hotel_age_restrictions()
    {
        $hotelAgeRestrictions = HotelAgeRestriction::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/age-restrictions');
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function test_can_show_hotel_age_restriction()
    {
        $hotelAgeRestriction = HotelAgeRestriction::factory()->create();
        $response = $this->request()->getJson("api/repo/age-restrictions/{$hotelAgeRestriction->id}");
        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $hotelAgeRestriction->id,
                'hotel_id' => $hotelAgeRestriction->hotel_id,
                'restriction_type_id' => $hotelAgeRestriction->restriction_type_id,
            ]);
    }
}
