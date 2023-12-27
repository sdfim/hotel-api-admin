<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Livewire\GeneralConfiguration\CreateGeneralConfigurationForm;
use App\Models\GeneralConfiguration;
use Livewire\Livewire;
use App\Models\User;

class GeneralConfigurationTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test
     * @return void
     */
    public function test_general_configuration_index_is_opening(): void
    {
        $this->auth();

        $response = $this->get('/admin/general-configuration');

        $response->assertStatus(200);
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_inserting_into_general_configuration(): void
    {
        $this->auth();

        $data = $this->generate_test_data();

        Livewire::test(CreateGeneralConfigurationForm::class)
            ->set('data', $data)
            ->call('save')
            ->assertRedirect(route('general_configuration'));

        $this->assertDatabaseHas('general_configurations', $data);
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_updating_of_general_configuration(): void
    {
        $this->auth();

        $general_configuration = GeneralConfiguration::factory()->create();

        $data = $this->generate_test_data();

        Livewire::test(CreateGeneralConfigurationForm::class, ['general_configuration' => $general_configuration])
            ->set('data', $data)
            ->call('save')
            ->assertRedirect(route('general_configuration'));

        $this->assertDatabaseHas('general_configurations', $data);
    }

    /**
     * @return array
     */
    protected function generate_test_data(): array
    {
        return [
            'time_supplier_requests' => 3,
            'time_reservations_kept' => 7,
            'currently_suppliers' => json_encode(['1']),
            'time_inspector_retained' => 60,
            'star_ratings' => 4,
            'stop_bookings' => 1,
        ];
    }


    /**
     * @return void
     */
    public function auth(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);
    }
}
