<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\HotelAffiliation;
use PHPUnit\Framework\Attributes\Test;

class HotelAffiliationControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_can_create_hotel_affiliation()
    {
        $data = HotelAffiliation::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/hotel-affiliations', $data);;
        $response->assertStatus(201)
            ->assertJsonFragment($data);
        $this->assertDatabaseHas('pd_hotel_affiliations', $data);
    }

    #[Test]
    public function test_can_update_hotel_affiliation()
    {
        $hotelAffiliation = HotelAffiliation::factory()->create();
        $data = HotelAffiliation::factory()->make(['hotel_id' => $hotelAffiliation->hotel_id])->toArray();
        $response = $this->request()->putJson("api/repo/hotel-affiliations/{$hotelAffiliation->id}", $data);
        $response->assertStatus(200)
            ->assertJsonFragment($data);
        $this->assertDatabaseHas('pd_hotel_affiliations', $data);
    }

    #[Test]
    public function test_can_delete_hotel_affiliation()
    {
        $hotelAffiliation = HotelAffiliation::factory()->create();
        $response = $this->request()->deleteJson("api/repo/hotel-affiliations/{$hotelAffiliation->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_hotel_affiliations', ['id' => $hotelAffiliation->id]);
    }

    #[Test]
    public function test_can_list_pd_hotel_affiliations()
    {
        $hotelAffiliations = HotelAffiliation::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/hotel-affiliations');
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function test_can_show_hotel_affiliation()
    {
        $hotelAffiliation = HotelAffiliation::factory()->create();
        $response = $this->request()->getJson("api/repo/hotel-affiliations/{$hotelAffiliation->id}");
        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $hotelAffiliation->id,
                'affiliation_name' => $hotelAffiliation->affiliation_name,
            ]);
    }
}
