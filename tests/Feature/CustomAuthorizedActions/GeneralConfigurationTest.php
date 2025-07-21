<?php

use App\Livewire\GeneralConfiguration\CreateGeneralConfigurationForm;
use App\Models\GeneralConfiguration;
use Livewire\Livewire;

function generate_test_data(): array
{
    return [
        'time_supplier_requests' => 3,
        'time_reservations_kept' => 7,
        'currently_suppliers' => json_encode(['1']),
        'time_inspector_retained' => 60,
        'content_supplier' => 'Expedia',
        'star_ratings' => 4,
        'stop_bookings' => 1,
    ];
}

test('general configuration index is opening', function () {
    $this->get('/admin/general-configuration')
        ->assertStatus(200);
});

test('possibility of inserting into general configuration', function () {
    $data = generate_test_data();

    Livewire::test(CreateGeneralConfigurationForm::class)
        ->set('data', $data)
        ->call('save')
        ->assertRedirect(route('general_configuration'));

    $this->assertDatabaseHas('general_configurations', $data);
});

test('possibility of updating of general configuration', function () {
    $general_configuration = GeneralConfiguration::factory()->create();

    $data = generate_test_data();

    Livewire::test(CreateGeneralConfigurationForm::class, ['general_configuration' => $general_configuration])
        ->set('data', $data)
        ->call('save')
        ->assertRedirect(route('general_configuration'));

    $this->assertDatabaseHas('general_configurations', $data);
});
