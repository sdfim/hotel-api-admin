<?php

namespace Tests\Feature\API\HotelContentRepository;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\HotelContentRepository\Models\ContactInformation;

// uses(RefreshDatabase::class);

test('can list product contact informations', function () {
    ContactInformation::factory()->count(3)->create();
    $response = test()->getJson('api/repo/contact-information');
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'contactable_id', 'first_name', 'last_name', 'contactable_type'],
        ],
        'message',
    ]);
});

test('can create product contact information', function () {
    $data = ContactInformation::factory()->make()->toArray();
    $response = test()->postJson('api/repo/contact-information', $data);
    $response->assertStatus(201);
    $response->assertJsonStructure([
        'data' => ['id', 'contactable_id', 'first_name', 'last_name', 'contactable_type'],
        'message',
    ]);
    $this->assertDatabaseHas('pd_contact_information', $data);
});

test('can show product contact information', function () {
    $contactInformation = ContactInformation::factory()->create();
    $response = test()->getJson("api/repo/contact-information/{$contactInformation->id}");
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => ['id', 'contactable_id', 'first_name', 'last_name', 'contactable_type'],
        'message',
    ]);
});

test('can update product contact information', function () {
    $contactInformation = ContactInformation::factory()->create();
    $data = ContactInformation::factory()->make(['contactable_id' => $contactInformation->contactable_id])->toArray();
    $response = test()->putJson("api/repo/contact-information/{$contactInformation->id}", $data);
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => ['id', 'contactable_id', 'first_name', 'last_name', 'contactable_type'],
            'message',
        ]);
    $this->assertDatabaseHas('pd_contact_information', $data);
});

test('can delete product contact information', function () {
    $contactInformation = ContactInformation::factory()->create();
    $response = test()->deleteJson("api/repo/contact-information/{$contactInformation->id}");
    $response->assertStatus(204);
    $this->assertDatabaseMissing('pd_contact_information', ['id' => $contactInformation->id]);
});