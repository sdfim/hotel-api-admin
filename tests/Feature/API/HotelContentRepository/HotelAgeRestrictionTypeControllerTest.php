<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\HotelAgeRestrictionType;

class HotelAgeRestrictionTypeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_hotel_age_restriction_type()
    {
        $data = HotelAgeRestrictionType::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/age-restriction-types', $data);
        $response->assertStatus(201)
            ->assertJsonFragment($data);
        $this->assertDatabaseHas('pd_hotel_age_restriction_types', $data);
    }

    public function test_can_update_hotel_age_restriction_type()
    {
        $hotelAgeRestrictionType = HotelAgeRestrictionType::factory()->create();
        $data = HotelAgeRestrictionType::factory()->make()->toArray();
        $response = $this->request()->putJson("api/repo/age-restriction-types/{$hotelAgeRestrictionType->id}", $data);
        $response->assertStatus(200)
            ->assertJsonFragment($data);
        $this->assertDatabaseHas('pd_hotel_age_restriction_types', $data);
    }

    public function test_can_delete_hotel_age_restriction_type()
    {
        $hotelAgeRestrictionType = HotelAgeRestrictionType::factory()->create();
        $response = $this->request()->deleteJson("api/repo/age-restriction-types/{$hotelAgeRestrictionType->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_hotel_age_restriction_types', ['id' => $hotelAgeRestrictionType->id]);
    }

    public function test_can_list_hotel_age_restriction_types()
    {
        $hotelAgeRestrictionTypes = HotelAgeRestrictionType::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/age-restriction-types');
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_show_hotel_age_restriction_type()
    {
        $hotelAgeRestrictionType = HotelAgeRestrictionType::factory()->create();
        $response = $this->request()->getJson("api/repo/age-restriction-types/{$hotelAgeRestrictionType->id}");
        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $hotelAgeRestrictionType->id,
                'name' => $hotelAgeRestrictionType->name,
            ]);
    }
}
