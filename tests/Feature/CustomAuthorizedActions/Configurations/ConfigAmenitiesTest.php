<?php

use App\Livewire\Configurations\Amenities\AmenitiesForm;
use App\Livewire\Configurations\Amenities\AmenitiesTable;
use App\Models\Configurations\ConfigAmenity;
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
    ConfigAmenity::factory(10)->create();

    $this->get(route('configurations.amenities.index'))
        ->assertSeeLivewire(AmenitiesTable::class)
        ->assertStatus(200);

    $component = Livewire::test(AmenitiesTable::class);

    $amenities = ConfigAmenity::limit(10)->get(['name']);
    foreach ($amenities as $amenity) {
        $component->assertSee([$amenity->name, $amenity->description]);
    }
});

test('admin create is opening', function () {
    $this->get(route('configurations.amenities.create'))
        ->assertSeeLivewire(AmenitiesForm::class)
        ->assertStatus(200);

    $component = Livewire::test(AmenitiesForm::class, ['configAmenity' => new ConfigAmenity]);

    $name = $this->faker->name();

    $component->set('data', [
        'name' => $name,
    ]);

    $component->call('edit');
    $component->assertRedirect(route('configurations.amenities.index'));

    $this->assertDatabaseHas('config_amenities', ['name' => $name]);
});

test('admin edit is opening', function () {
    $configAmenity = ConfigAmenity::factory()->create();

    $this->get(route('configurations.amenities.edit', $configAmenity->id))
        ->assertSeeLivewire(AmenitiesForm::class)
        ->assertStatus(200);

    $component = Livewire::test(AmenitiesForm::class, ['configAmenity' => $configAmenity]);

    $name = $this->faker->name();

    $component->set('data', [
        'name' => $name,
    ]);

    $component->call('edit');
    $component->assertRedirect(route('configurations.amenities.index'));

    $this->assertDatabaseHas('config_amenities', ['name' => $name]);
});
