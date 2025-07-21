<?php

use App\Livewire\Configurations\AttributeCategories\AttributeCategoriesForm;
use App\Livewire\Configurations\AttributeCategories\AttributeCategoriesTable;
use App\Models\Configurations\ConfigAttributeCategory;
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

test('attribute categories index is opening', function () {
    ConfigAttributeCategory::factory(10)->create();

    $this->get(route('configurations.attribute-categories.index'))
        ->assertSeeLivewire(AttributeCategoriesTable::class)
        ->assertStatus(200);

    $component = Livewire::test(AttributeCategoriesTable::class);

    $categories = ConfigAttributeCategory::limit(10)->get(['name']);
    foreach ($categories as $category) {
        $component->assertSee([$category->name]);
    }
});

test('attribute categories create is opening', function () {
    $this->get(route('configurations.attribute-categories.create'))
        ->assertSeeLivewire(AttributeCategoriesForm::class)
        ->assertStatus(200);

    $component = Livewire::test(AttributeCategoriesForm::class, ['configAttributeCategory' => new ConfigAttributeCategory]);

    $name = $this->faker->name();

    $component->set('data', [
        'name' => $name,
    ]);

    $component->call('edit');
    $component->assertRedirect(route('configurations.attribute-categories.index'));

    $this->assertDatabaseHas('config_attribute_categories', ['name' => $name]);
});
