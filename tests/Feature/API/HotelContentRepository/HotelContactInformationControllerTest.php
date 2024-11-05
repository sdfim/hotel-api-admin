<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\HotelContactInformation;
use PHPUnit\Framework\Attributes\Test;

class HotelContactInformationControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_can_list_hotel_contact_informations()
    {
        HotelContactInformation::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/hotel-contact-information');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'hotel_id', 'first_name', 'last_name', 'email', 'phone']
            ],
            'message'
        ]);
    }

    #[Test]
    public function test_can_create_hotel_contact_information()
    {
        $data = HotelContactInformation::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/hotel-contact-information', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['id', 'hotel_id', 'first_name', 'last_name', 'email', 'phone'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_hotel_contact_information', $data);
    }

    #[Test]
    public function test_can_show_hotel_contact_information()
    {
        $contactInformation = HotelContactInformation::factory()->create();
        $response = $this->request()->getJson("api/repo/hotel-contact-information/{$contactInformation->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'hotel_id', 'first_name', 'last_name', 'email', 'phone'],
            'message'
        ]);
    }

    #[Test]
    public function test_can_update_hotel_contact_information()
    {
        $contactInformation = HotelContactInformation::factory()->create();
        $data = HotelContactInformation::factory()->make(['hotel_id' => $contactInformation->hotel_id])->toArray();
        $response = $this->request()->putJson("api/repo/hotel-contact-information/{$contactInformation->id}", $data);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'hotel_id', 'first_name', 'last_name', 'email', 'phone'],
                'message'
            ]);
        $this->assertDatabaseHas('pd_hotel_contact_information', $data);
    }

    #[Test]
    public function test_can_delete_hotel_contact_information()
    {
        $contactInformation = HotelContactInformation::factory()->create();
        $response = $this->request()->deleteJson("api/repo/hotel-contact-information/{$contactInformation->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_hotel_contact_information', ['id' => $contactInformation->id]);
    }
}
