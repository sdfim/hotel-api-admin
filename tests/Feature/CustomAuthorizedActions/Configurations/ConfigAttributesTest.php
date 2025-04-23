<?php

namespace Tests\Feature\CustomAuthorizedActions\Configurations;

use App\Livewire\Configurations\Attributes\AttributesForm;
use App\Livewire\Configurations\Attributes\AttributesTable;
use App\Models\Configurations\ConfigAttribute;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\CustomAuthorizedActions\CustomAuthorizedActionsTestCase;

class ConfigAttributesTest extends CustomAuthorizedActionsTestCase
{
    use WithFaker;

    #[Test]
    public function test_admin_index_is_opening(): void
    {
        ConfigAttribute::factory(10)->create();

        $this->get(route('configurations.attributes.index'))
            ->assertSeeLivewire(AttributesTable::class)
            ->assertStatus(200);

        $component = Livewire::test(AttributesTable::class);

        $attributes = ConfigAttribute::limit(10)->get(['name']);
        foreach ($attributes as $attribute) {
            $component->assertSee([$attribute->name, $attribute->default_value]);
        }
    }

    #[Test]
    public function test_admin_create_is_opening(): void
    {
        $this->get(route('configurations.attributes.create'))
            ->assertSeeLivewire(AttributesForm::class)
            ->assertStatus(200);

        $component = Livewire::test(AttributesForm::class, ['configAttribute' => new ConfigAttribute()]);

        $name = $this->faker->name;

        $component->set('data', [
            'name' => $name,
        ]);

        $component->call('edit');
        $component->assertRedirect(route('configurations.attributes.index'));

        $this->assertDatabaseHas('config_attributes', ['name' => $name]);
    }

    #[Test]
    public function test_admin_edit_is_opening(): void
    {
        $configAttribute = ConfigAttribute::factory()->create();

        $this->get(route('configurations.attributes.edit', $configAttribute->id))
            ->assertSeeLivewire(AttributesForm::class)
            ->assertStatus(200);

        $component = Livewire::test(AttributesForm::class, ['configAttribute' => $configAttribute]);

        $name = $this->faker->name;

        $component->set('data', [
            'name' => $name,
        ]);

        $component->call('edit');
        $component->assertRedirect(route('configurations.attributes.index'));

        $this->assertDatabaseHas('config_attributes', ['name' => $name]);
    }
}
