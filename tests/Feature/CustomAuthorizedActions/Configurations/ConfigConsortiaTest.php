<?php

use App\Livewire\Configurations\Consortia\ConsortiaForm;
use App\Livewire\Configurations\Consortia\ConsortiaTable;
use App\Models\Configurations\ConfigConsortium;
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
    ConfigConsortium::factory(10)->create();

    $this->get(route('configurations.consortia.index'))
        ->assertSeeLivewire(ConsortiaTable::class)
        ->assertStatus(200);

    $component = Livewire::test(ConsortiaTable::class);

    $consortia = ConfigConsortium::limit(10)->get(['name', 'description']);
    foreach ($consortia as $consortium) {
        $component->assertSee([$consortium->name, $consortium->description]);
    }
});

test('admin create is opening', function () {
    $this->get(route('configurations.consortia.create'))
        ->assertSeeLivewire(ConsortiaForm::class)
        ->assertStatus(200);

    $component = Livewire::test(ConsortiaForm::class, ['configConsortium' => new ConfigConsortium]);

    $name = $this->faker->name();
    $description = $this->faker->sentence();

    $component->set('data', [
        'name' => $name,
        'description' => $description,
    ]);

    $component->call('edit');
    $component->assertRedirect(route('configurations.consortia.index'));

    $this->assertDatabaseHas('config_consortia', ['name' => $name, 'description' => $description]);
});

test('admin edit is opening', function () {
    $configConsortium = ConfigConsortium::factory()->create();

    $this->get(route('configurations.consortia.edit', $configConsortium->id))
        ->assertSeeLivewire(ConsortiaForm::class)
        ->assertStatus(200);

    $component = Livewire::test(ConsortiaForm::class, ['configConsortium' => $configConsortium]);

    $name = $this->faker->name();
    $description = $this->faker->sentence();

    $component->set('data', [
        'name' => $name,
        'description' => $description,
    ]);

    $component->call('edit');
    $component->assertRedirect(route('configurations.consortia.index'));

    $this->assertDatabaseHas('config_consortia', ['name' => $name, 'description' => $description]);
});
