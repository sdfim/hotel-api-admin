<?php

namespace Tests\Feature\CustomAuthorizedActions\Configurations;

use App\Livewire\Configurations\DescriptiveTypes\DescriptiveTypesForm;
use App\Livewire\Configurations\DescriptiveTypes\DescriptiveTypesTable;
use App\Models\Configurations\ConfigDescriptiveType;
use App\Models\Enums\DescriptiveLocation;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\CustomAuthorizedActions\CustomAuthorizedActionsTestCase;

class ConfigDescriptiveTypesTest extends CustomAuthorizedActionsTestCase
{
    use WithFaker;

    #[Test]
    public function test_admin_index_is_opening(): void
    {
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
    }

    #[Test]
    public function test_admin_create_is_opening(): void
    {
        $this->get(route('configurations.descriptive-types.create'))
            ->assertSeeLivewire(DescriptiveTypesForm::class)
            ->assertStatus(200);

        $component = Livewire::test(DescriptiveTypesForm::class, [
            'configDescriptiveType' => new ConfigDescriptiveType(),
        ]);

        $name = $this->faker->name;
        $location = $this->faker->randomElement(DescriptiveLocation::cases());
        $type = $this->faker->word;
        $description = $this->faker->sentence;

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
    }

    #[Test]
    public function test_admin_edit_is_opening(): void
    {
        $configDescriptiveType = ConfigDescriptiveType::factory()->create();

        $this->get(route('configurations.descriptive-types.edit', $configDescriptiveType->id))
            ->assertSeeLivewire(DescriptiveTypesForm::class)
            ->assertStatus(200);

        $component = Livewire::test(DescriptiveTypesForm::class, [
            'configDescriptiveType' => $configDescriptiveType,
        ]);

        $name = $this->faker->name;
        $location = $this->faker->randomElement(DescriptiveLocation::cases());
        $type = $this->faker->word;
        $description = $this->faker->sentence;

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
    }
}
