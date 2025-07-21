<?php

use App\Livewire\Configurations\ServiceTypes\ServiceTypesForm;
use App\Livewire\Configurations\ServiceTypes\ServiceTypesTable;
use App\Models\Configurations\ConfigServiceType;
use Livewire\Livewire;
// use Tests\TestCase;
use Tests\Feature\CustomAuthorizedActions\CustomAuthorizedActionsTestCase;
use Illuminate\Foundation\Testing\WithFaker;

// uses(TestCase::class);
// uses(CustomAuthorizedActionsTestCase::class);
uses(WithFaker::class);

beforeEach(function () {
    $this->auth();
});

test('admin index is opening', function () {
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
});

test('admin create is opening', function () {
    $this->get(route('configurations.service-types.create'))
        ->assertSeeLivewire(ServiceTypesForm::class)
        ->assertStatus(200);

    $component = Livewire::test(ServiceTypesForm::class, ['configServiceType' => new ConfigServiceType]);

    $name = $this->faker->name();
    $description = $this->faker->sentence();

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
});

test('admin edit is opening', function () {
    $configServiceType = ConfigServiceType::factory()->create();

    $this->get(route('configurations.service-types.edit', $configServiceType->id))
        ->assertSeeLivewire(ServiceTypesForm::class)
        ->assertStatus(200);

    $component = Livewire::test(ServiceTypesForm::class, ['configServiceType' => $configServiceType]);

    $name = $this->faker->name();
    $description = $this->faker->sentence();

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
});
