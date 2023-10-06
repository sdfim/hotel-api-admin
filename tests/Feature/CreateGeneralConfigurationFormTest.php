<?php

namespace Tests\Feature;

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

    // Тест на створення нової загальної конфігурації
    public function testCanCreateGeneralConfiguration()
    {
        $this->auth();

        $data = $this->generateTestData();

        Livewire::test(CreateGeneralConfigurationForm::class)
            ->set('data', $data)
            ->call('save')
            ->assertRedirect(route('general_configuration'));

        // Перевірте, що нова загальна конфігурація створена в базі даних
        $this->assertDatabaseHas('general_configurations', $data);
    }

    // Тест на оновлення існуючої загальної конфігурації
    public function testCanUpdateGeneralConfiguration()
    {
        $this->auth();

        // Створюємо тестову загальну конфігурацію
        $general_configuration = GeneralConfiguration::factory()->create();

        $data = $this->generateTestData();

        Livewire::test(CreateGeneralConfigurationForm::class, ['general_configuration' => $general_configuration])
            ->set('data', $data)
            ->call('save')
            ->assertRedirect(route('general_configuration'));

        // Перевірте, що існуюча загальна конфігурація оновлена в базі даних
        $this->assertDatabaseHas('general_configurations', $data);
    }

    // Генерує тестові дані для загальної конфігурації
    protected function generateTestData()
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

    public function auth()
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
    }
}
