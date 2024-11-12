<?php

namespace Tests\Feature\CustomAuthorizedActions;

use App\Livewire\GeneralConfiguration\CreateGeneralConfigurationForm;
use App\Models\GeneralConfiguration;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;

class GeneralConfigurationTest extends CustomAuthorizedActionsTestCase
{
    #[Test]
    public function test_general_configuration_index_is_opening(): void
    {
        $response = $this->get('/admin/general-configuration');

        $response->assertStatus(200);
    }

    #[Test]
    public function test_possibility_of_inserting_into_general_configuration(): void
    {
        $data = $this->generate_test_data();

        Livewire::test(CreateGeneralConfigurationForm::class)
            ->set('data', $data)
            ->call('save')
            ->assertRedirect(route('general_configuration'));

        $this->assertDatabaseHas('general_configurations', $data);
    }

    #[Test]
    public function test_possibility_of_updating_of_general_configuration(): void
    {
        $general_configuration = GeneralConfiguration::factory()->create();

        $data = $this->generate_test_data();

        Livewire::test(CreateGeneralConfigurationForm::class, ['general_configuration' => $general_configuration])
            ->set('data', $data)
            ->call('save')
            ->assertRedirect(route('general_configuration'));

        $this->assertDatabaseHas('general_configurations', $data);
    }

    protected function generate_test_data(): array
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
}
