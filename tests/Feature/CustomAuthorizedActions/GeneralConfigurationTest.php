<?php

use App\Livewire\GeneralConfiguration\CreateGeneralConfigurationForm;
use App\Models\GeneralConfiguration;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Livewire\Livewire;

function generate_test_data($supplierId = 1): array
{
    return [
        'time_supplier_requests' => 3,
        'time_reservations_kept' => 7,
        'currently_suppliers' => [(string) $supplierId],
        'time_inspector_retained' => 60,
        'content_supplier' => 'Expedia',
        'star_ratings' => 4,
        'stop_bookings' => 1,
        'default_currency' => 'USD',
    ];
}

beforeEach(function () {
    config(['booking-suppliers.connected_suppliers' => 'Expedia,HBSI']);

    $this->supplier = Supplier::factory()->create(['name' => 'HBSI']);
    $this->user = User::factory()->create();
    $role = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
    $this->user->roles()->attach($role);

    $this->actingAs($this->user);
});

test('general configuration index is opening', function () {
    $this->get('/admin/general-configuration')
        ->assertStatus(200);
});

test('possibility of inserting into general configuration', function () {
    $data = generate_test_data($this->supplier->id);

    Livewire::test(CreateGeneralConfigurationForm::class)
        ->set('data', $data)
        ->call('save')
        ->assertRedirect(route('general_configuration'));

    $this->assertDatabaseHas('general_configurations', [
        'time_supplier_requests' => 3,
        'time_reservations_kept' => 7,
        'time_inspector_retained' => 60,
        'content_supplier' => 'Expedia',
        'star_ratings' => 4,
        'stop_bookings' => 1,
        'default_currency' => 'USD',
    ]);
});

test('possibility of updating of general configuration', function () {
    $general_configuration = GeneralConfiguration::factory()->create();

    $data = generate_test_data($this->supplier->id);

    Livewire::test(CreateGeneralConfigurationForm::class, ['general_configuration' => $general_configuration])
        ->set('data', $data)
        ->call('save')
        ->assertRedirect(route('general_configuration'));

    $this->assertDatabaseHas('general_configurations', [
        'id' => $general_configuration->id,
        'time_supplier_requests' => 3,
        'time_reservations_kept' => 7,
        'time_inspector_retained' => 60,
        'content_supplier' => 'Expedia',
        'star_ratings' => 4,
        'stop_bookings' => 1,
        'default_currency' => 'USD',
    ]);
});
