<?php

use App\Livewire\Configurations\Attributes\AttributesForm;
use App\Livewire\Configurations\Attributes\AttributesTable;
use App\Models\Configurations\ConfigAttribute;
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
    ConfigAttribute::factory(10)->create();

    $this->get(route('configurations.attributes.index'))
        ->assertSeeLivewire(AttributesTable::class)
        ->assertStatus(200);

    $component = Livewire::test(AttributesTable::class);

    $attributes = ConfigAttribute::limit(10)->get(['name']);
    foreach ($attributes as $attribute) {
        $component->assertSee([$attribute->name, $attribute->default_value]);
    }
});

test('admin create is opening', function () {
    $this->get(route('configurations.attributes.create'))
        ->assertSeeLivewire(AttributesForm::class)
        ->assertStatus(200);

    $component = Livewire::test(AttributesForm::class, ['configAttribute' => new ConfigAttribute]);

    $name = $this->faker->name();

    $component->set('data', [
        'name' => $name,
    ]);

    $component->call('edit');
    $component->assertRedirect(route('configurations.attributes.index'));

    $this->assertDatabaseHas('config_attributes', ['name' => $name]);
});

test('admin edit is opening', function () {
    $configAttribute = ConfigAttribute::factory()->create();

    $this->get(route('configurations.attributes.edit', $configAttribute->id))
        ->assertSeeLivewire(AttributesForm::class)
        ->assertStatus(200);

    $component = Livewire::test(AttributesForm::class, ['configAttribute' => $configAttribute]);

    $name = $this->faker->name();

    $component->set('data', [
        'name' => $name,
    ]);

    $component->call('edit');
    $component->assertRedirect(route('configurations.attributes.index'));

    $this->assertDatabaseHas('config_attributes', ['name' => $name]);
});
