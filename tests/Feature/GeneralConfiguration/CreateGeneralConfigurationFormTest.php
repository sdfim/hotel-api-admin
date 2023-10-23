<?php

namespace Tests\Feature\GeneralConfiguration;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;
use App\Livewire\GeneralConfiguration\CreateGeneralConfigurationForm;
use App\Models\GeneralConfiguration;

class CreateGeneralConfigurationFormTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_inserting_into_general_configuration()
    {
        // $this->auth();

        // $data = $this->generate_test_data();

        // Livewire::test(CreateGeneralConfigurationForm::class)
        //     ->set('data', $data)
        //     ->call('save')
        //     ->assertRedirect(route('general_configuration'));

        // $this->assertDatabaseHas('general_configurations', $data);
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_updating_of_general_configuration()
    {
        // $this->auth();

        // $general_configuration = GeneralConfiguration::factory()->create();

        // $data = $this->generate_test_data();

        // Livewire::test(CreateGeneralConfigurationForm::class, ['general_configuration' => $general_configuration])
        //     ->set('data', $data)
        //     ->call('save')
        //     ->assertRedirect(route('general_configuration'));

        // $this->assertDatabaseHas('general_configurations', $data);
    }

    /**
     * @return array
     */
    protected function generate_test_data(): array
    {
        return [
            'time_supplier_requests' => $this->faker->randomNumber(),
            'time_reservations_kept' => $this->faker->randomNumber(),
            'currently_suppliers' => $this->faker->sentence(),
            'time_inspector_retained' => $this->faker->randomNumber(),
            'star_ratings' => now(),
            'stop_bookings' => now(),
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
