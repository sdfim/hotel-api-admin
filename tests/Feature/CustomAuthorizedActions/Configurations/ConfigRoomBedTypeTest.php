<?php

use App\Livewire\Configurations\RoomBedTypes\RoomBedTypeForm;
use App\Livewire\Configurations\RoomBedTypes\RoomBedTypeTable;
use App\Models\Configurations\ConfigRoomBedType;
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
    ConfigRoomBedType::factory(10)->create();

    $this->get(route('configurations.room-bed-types.index'))
        ->assertSeeLivewire(RoomBedTypeTable::class)
        ->assertStatus(200);

    $component = Livewire::test(RoomBedTypeTable::class);

    $roomBedTypes = ConfigRoomBedType::limit(10)->get(['name']);
    foreach ($roomBedTypes as $roomBedType) {
        $component->assertSee($roomBedType->name);
    }
});

test('admin create is opening', function () {
    $this->get(route('configurations.room-bed-types.create'))
        ->assertSeeLivewire(RoomBedTypeForm::class)
        ->assertStatus(200);

    $component = Livewire::test(RoomBedTypeForm::class, ['configRoomBedType' => new ConfigRoomBedType]);

    $name = $this->faker->name();

    $component->set('data', [
        'name' => $name,
    ]);

    $component->call('edit');
    $component->assertRedirect(route('configurations.room-bed-types.index'));

    $this->assertDatabaseHas('config_room_bed_types', ['name' => $name]);
});

test('admin edit is opening', function () {
    $configRoomBedType = ConfigRoomBedType::factory()->create();

    $this->get(route('configurations.room-bed-types.edit', $configRoomBedType->id))
        ->assertSeeLivewire(RoomBedTypeForm::class)
        ->assertStatus(200);

    $component = Livewire::test(RoomBedTypeForm::class, ['configRoomBedType' => $configRoomBedType]);

    $name = $this->faker->name();

    $component->set('data', [
        'name' => $name,
    ]);

    $component->call('edit');
    $component->assertRedirect(route('configurations.room-bed-types.index'));

    $this->assertDatabaseHas('config_room_bed_types', ['name' => $name]);
});
