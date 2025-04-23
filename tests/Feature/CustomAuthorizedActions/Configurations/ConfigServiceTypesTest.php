<?php

namespace Tests\Feature\CustomAuthorizedActions\Configurations;

use App\Livewire\Configurations\ServiceTypes\ServiceTypesForm;
use App\Livewire\Configurations\ServiceTypes\ServiceTypesTable;
use App\Models\Configurations\ConfigServiceType;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\CustomAuthorizedActions\CustomAuthorizedActionsTestCase;

class ConfigServiceTypesTest extends CustomAuthorizedActionsTestCase
{
    use WithFaker;

    #[Test]
    public function test_admin_index_is_opening(): void
    {
        ConfigServiceType::factory(10)->create();

        $this->get(route('configurations.service-types.index'))
            ->assertSeeLivewire(ServiceTypesTable::class)
            ->assertStatus(200);

        $component = Livewire::test(ServiceTypesTable::class);

        $serviceTypes = ConfigServiceType::limit(10)->get(['name', 'description']);
        foreach ($serviceTypes as $serviceType) {
            $component->assertSee([
                $serviceType->name,
                $serviceType->description,
                $serviceType->cost,
            ]);
        }
    }

    #[Test]
    public function test_admin_create_is_opening(): void
    {
        $this->get(route('configurations.service-types.create'))
            ->assertSeeLivewire(ServiceTypesForm::class)
            ->assertStatus(200);

        $component = Livewire::test(ServiceTypesForm::class, ['configServiceType' => new ConfigServiceType()]);

        $name = $this->faker->name;
        $description = $this->faker->sentence;

        $component->set('data', [
            'name' => $name,
            'description' => $description,
        ]);

        $component->call('edit');
        $component->assertRedirect(route('configurations.service-types.index'));

        $this->assertDatabaseHas('config_service_types', [
            'name' => $name,
            'description' => $description,
        ]);
    }

    #[Test]
    public function test_admin_edit_is_opening(): void
    {
        $configServiceType = ConfigServiceType::factory()->create();

        $this->get(route('configurations.service-types.edit', $configServiceType->id))
            ->assertSeeLivewire(ServiceTypesForm::class)
            ->assertStatus(200);

        $component = Livewire::test(ServiceTypesForm::class, ['configServiceType' => $configServiceType]);

        $name = $this->faker->name;
        $description = $this->faker->sentence;

        $component->set('data', [
            'name' => $name,
            'description' => $description,
        ]);

        $component->call('edit');
        $component->assertRedirect(route('configurations.service-types.index'));

        $this->assertDatabaseHas('config_service_types', [
            'name' => $name,
            'description' => $description,
        ]);
    }
}
