<?php

namespace Tests\Feature\CustomAuthorizedActions\Configurations;

use App\Livewire\Configurations\Amenities\AmenitiesForm;
use App\Livewire\Configurations\Amenities\AmenitiesTable;
use App\Models\Configurations\ConfigAmenity;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\CustomAuthorizedActions\CustomAuthorizedActionsTestCase;

class ConfigAmenitiesTest extends CustomAuthorizedActionsTestCase
{
    use WithFaker;

    #[Test]
    public function test_admin_index_is_opening(): void
    {
        ConfigAmenity::factory(10)->create();

        $this->get(route('configurations.amenities.index'))
            ->assertSeeLivewire(AmenitiesTable::class)
            ->assertStatus(200);

        $component = Livewire::test(AmenitiesTable::class);

        $amenities = ConfigAmenity::limit(10)->get(['name']);
        foreach ($amenities as $amenity) {
            $component->assertSee([$amenity->name, $amenity->description]);
        }
    }

    #[Test]
    public function test_admin_create_is_opening(): void
    {
        $this->get(route('configurations.amenities.create'))
            ->assertSeeLivewire(AmenitiesForm::class)
            ->assertStatus(200);

        $component = Livewire::test(AmenitiesForm::class, ['configAmenity' => new ConfigAmenity()]);

        $name = $this->faker->name;

        $component->set('data', [
            'name' => $name,
        ]);

        $component->call('edit');
        $component->assertRedirect(route('configurations.amenities.index'));

        $this->assertDatabaseHas('config_amenities', ['name' => $name]);
    }

    #[Test]
    public function test_admin_edit_is_opening(): void
    {
        $configAmenity = ConfigAmenity::factory()->create();

        $this->get(route('configurations.amenities.edit', $configAmenity->id))
            ->assertSeeLivewire(AmenitiesForm::class)
            ->assertStatus(200);

        $component = Livewire::test(AmenitiesForm::class, ['configAmenity' => $configAmenity]);

        $name = $this->faker->name;

        $component->set('data', [
            'name' => $name,
        ]);

        $component->call('edit');
        $component->assertRedirect(route('configurations.amenities.index'));

        $this->assertDatabaseHas('config_amenities', ['name' => $name]);
    }
}
