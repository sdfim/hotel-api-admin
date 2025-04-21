<?php

namespace Feature\CustomAuthorizedActions\Configurations;

use App\Livewire\Configurations\AttributeCategories\AttributeCategoriesForm;
use App\Livewire\Configurations\AttributeCategories\AttributeCategoriesTable;
use App\Models\Configurations\ConfigAttributeCategory;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\CustomAuthorizedActions\CustomAuthorizedActionsTestCase;

class ConfigAttributeCategoriesTest extends CustomAuthorizedActionsTestCase
{
    use WithFaker;

    #[Test]
    public function test_attribute_categories_index_is_opening(): void
    {
        ConfigAttributeCategory::factory(10)->create();

        $this->get(route('configurations.attribute-categories.index'))
            ->assertSeeLivewire(AttributeCategoriesTable::class)
            ->assertStatus(200);

        $component = Livewire::test(AttributeCategoriesTable::class);

        $categories = ConfigAttributeCategory::limit(10)->get(['name']);
        foreach ($categories as $category) {
            $component->assertSee([$category->name]);
        }
    }

    #[Test]
    public function test_attribute_categories_create_is_opening(): void
    {
        $this->get(route('configurations.attribute-categories.create'))
            ->assertSeeLivewire(AttributeCategoriesForm::class)
            ->assertStatus(200);

        $component = Livewire::test(AttributeCategoriesForm::class, ['configAttributeCategory' => new ConfigAttributeCategory]);

        $name = $this->faker->name;

        $component->set('data', [
            'name' => $name,
        ]);

        $component->call('edit');
        $component->assertRedirect(route('configurations.attribute-categories.index'));

        $this->assertDatabaseHas('config_attribute_categories', ['name' => $name]);
    }
}
