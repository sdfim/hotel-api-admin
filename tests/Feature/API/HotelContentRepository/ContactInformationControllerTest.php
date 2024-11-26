<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\ContactInformation;
use PHPUnit\Framework\Attributes\Test;

class ContactInformationControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_can_list_product_contact_informations()
    {
        ContactInformation::factory()->count(3)->create();
        $response = $this->request()->getJson('api/repo/contact-information');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'contactable_id', 'first_name', 'last_name', 'email', 'phone']
            ],
            'message'
        ]);
    }

    #[Test]
    public function test_can_create_product_contact_information()
    {
        $data = ContactInformation::factory()->make()->toArray();
        $response = $this->request()->postJson('api/repo/contact-information', $data);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['id', 'contactable_id', 'first_name', 'last_name', 'email', 'phone'],
            'message'
        ]);
        $this->assertDatabaseHas('pd_contact_information', $data);
    }

    #[Test]
    public function test_can_show_product_contact_information()
    {
        $contactInformation = ContactInformation::factory()->create();
        $response = $this->request()->getJson("api/repo/contact-information/{$contactInformation->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'contactable_id', 'first_name', 'last_name', 'email', 'phone'],
            'message'
        ]);
    }

    #[Test]
    public function test_can_update_product_contact_information()
    {
        $contactInformation = ContactInformation::factory()->create();
        $data = ContactInformation::factory()->make(['contactable_id' => $contactInformation->contactable_id])->toArray();
        $response = $this->request()->putJson("api/repo/contact-information/{$contactInformation->id}", $data);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'contactable_id', 'first_name', 'last_name', 'email', 'phone'],
                'message'
            ]);
        $this->assertDatabaseHas('pd_contact_information', $data);
    }

    #[Test]
    public function test_can_delete_product_contact_information()
    {
        $contactInformation = ContactInformation::factory()->create();
        $response = $this->request()->deleteJson("api/repo/contact-information/{$contactInformation->id}");
        $response->assertStatus(204);
        $this->assertDatabaseMissing('pd_contact_information', ['id' => $contactInformation->id]);
    }
}
