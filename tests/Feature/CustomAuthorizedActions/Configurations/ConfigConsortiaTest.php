<?php

namespace Tests\Feature\CustomAuthorizedActions\Configurations;

use App\Livewire\Configurations\Consortia\ConsortiaForm;
use App\Livewire\Configurations\Consortia\ConsortiaTable;
use App\Models\Configurations\ConfigConsortium;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\CustomAuthorizedActions\CustomAuthorizedActionsTestCase;

class ConfigConsortiaTest extends CustomAuthorizedActionsTestCase
{
    use WithFaker;

    #[Test]
    public function test_admin_index_is_opening(): void
    {
        ConfigConsortium::factory(10)->create();

        $this->get(route('configurations.consortia.index'))
            ->assertSeeLivewire(ConsortiaTable::class)
            ->assertStatus(200);

        $component = Livewire::test(ConsortiaTable::class);

        $consortia = ConfigConsortium::limit(10)->get(['name', 'description']);
        foreach ($consortia as $consortium) {
            $component->assertSee([$consortium->name, $consortium->description]);
        }
    }

    #[Test]
    public function test_admin_create_is_opening(): void
    {
        $this->get(route('configurations.consortia.create'))
            ->assertSeeLivewire(ConsortiaForm::class)
            ->assertStatus(200);

        $component = Livewire::test(ConsortiaForm::class, ['configConsortium' => new ConfigConsortium()]);

        $name = $this->faker->name;
        $description = $this->faker->sentence;

        $component->set('data', [
            'name' => $name,
            'description' => $description,
        ]);

        $component->call('edit');
        $component->assertRedirect(route('configurations.consortia.index'));

        $this->assertDatabaseHas('config_consortia', ['name' => $name, 'description' => $description]);
    }

    #[Test]
    public function test_admin_edit_is_opening(): void
    {
        $configConsortium = ConfigConsortium::factory()->create();

        $this->get(route('configurations.consortia.edit', $configConsortium->id))
            ->assertSeeLivewire(ConsortiaForm::class)
            ->assertStatus(200);

        $component = Livewire::test(ConsortiaForm::class, ['configConsortium' => $configConsortium]);

        $name = $this->faker->name;
        $description = $this->faker->sentence;

        $component->set('data', [
            'name' => $name,
            'description' => $description,
        ]);

        $component->call('edit');
        $component->assertRedirect(route('configurations.consortia.index'));

        $this->assertDatabaseHas('config_consortia', ['name' => $name, 'description' => $description]);
    }
}
