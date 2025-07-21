<?php

use App\Livewire\Configurations\DescriptiveTypes\DescriptiveTypesForm;
use App\Livewire\Configurations\DescriptiveTypes\DescriptiveTypesTable;
use App\Models\Configurations\ConfigDescriptiveType;
use App\Models\Enums\DescriptiveLocationEnum;
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
    ConfigDescriptiveType::factory(10)->create();

    $this->get(route('configurations.descriptive-types.index'))
        ->assertSeeLivewire(DescriptiveTypesTable::class)
        ->assertStatus(200);

    $component = Livewire::test(DescriptiveTypesTable::class);

    $descriptiveTypes = ConfigDescriptiveType::limit(10)->get([
        'name',
        'location',
        'type',
        'description',
    ]);
    foreach ($descriptiveTypes as $descriptiveType) {
        $component->assertSee([
            $descriptiveType->name,
            $descriptiveType->location,
            $descriptiveType->type,
            $descriptiveType->description,
        ]);
    }
});

test('admin create is opening', function () {
    $this->get(route('configurations.descriptive-types.create'))
        ->assertSeeLivewire(DescriptiveTypesForm::class)
        ->assertStatus(200);

    $component = Livewire::test(DescriptiveTypesForm::class, [
        'configDescriptiveType' => new ConfigDescriptiveType,
    ]);

    $name = $this->faker->name();
    $location = $this->faker->randomElement(DescriptiveLocationEnum::cases());
    $type = $this->faker->word();
    $description = $this->faker->sentence();

    $component->set('data', [
        'name' => $name,
        'location' => $location,
        'type' => $type,
        'description' => $description,
    ]);

    $component->call('edit');
    $component->assertRedirect(route('configurations.descriptive-types.index'));

    $this->assertDatabaseHas('config_descriptive_types', [
        'name' => $name,
        'location' => $location,
        'type' => $type,
        'description' => $description,
    ]);
});

test('admin edit is opening', function () {
    $configDescriptiveType = ConfigDescriptiveType::factory()->create();

    $this->get(route('configurations.descriptive-types.edit', $configDescriptiveType->id))
        ->assertSeeLivewire(DescriptiveTypesForm::class)
        ->assertStatus(200);

    $component = Livewire::test(DescriptiveTypesForm::class, [
        'configDescriptiveType' => $configDescriptiveType,
    ]);

    $name = $this->faker->name();
    $location = $this->faker->randomElement(DescriptiveLocationEnum::cases());
    $type = $this->faker->word();
    $description = $this->faker->sentence();

    $component->set('data', [
        'name' => $name,
        'location' => $location,
        'type' => $type,
        'description' => $description,
    ]);

    $component->call('edit');
    $component->assertRedirect(route('configurations.descriptive-types.index'));

    $this->assertDatabaseHas('config_descriptive_types', [
        'name' => $name,
        'location' => $location,
        'type' => $type,
        'description' => $description,
    ]);
});
