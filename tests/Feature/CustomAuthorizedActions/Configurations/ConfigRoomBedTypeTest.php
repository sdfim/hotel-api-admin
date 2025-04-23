<?php

namespace Tests\Feature\CustomAuthorizedActions\Configurations;

use App\Livewire\Configurations\RoomBedTypes\RoomBedTypeForm;
use App\Livewire\Configurations\RoomBedTypes\RoomBedTypeTable;
use App\Models\Configurations\ConfigRoomBedType;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\CustomAuthorizedActions\CustomAuthorizedActionsTestCase;

class ConfigRoomBedTypeTest extends CustomAuthorizedActionsTestCase
{
    use WithFaker;

    #[Test]
    public function test_admin_index_is_opening(): void
    {
        ConfigRoomBedType::factory(10)->create();

        $this->get(route('configurations.room-bed-types.index'))
            ->assertSeeLivewire(RoomBedTypeTable::class)
            ->assertStatus(200);

        $component = Livewire::test(RoomBedTypeTable::class);

        $roomBedTypes = ConfigRoomBedType::limit(10)->get(['name']);
        foreach ($roomBedTypes as $roomBedType) {
            $component->assertSee($roomBedType->name);
        }
    }

    #[Test]
    public function test_admin_create_is_opening(): void
    {
        $this->get(route('configurations.room-bed-types.create'))
            ->assertSeeLivewire(RoomBedTypeForm::class)
            ->assertStatus(200);

        $component = Livewire::test(RoomBedTypeForm::class, ['configRoomBedType' => new ConfigRoomBedType]);

        $name = $this->faker->name;

        $component->set('data', [
            'name' => $name,
        ]);

        $component->call('edit');
        $component->assertRedirect(route('configurations.room-bed-types.index'));

        $this->assertDatabaseHas('config_room_bed_types', ['name' => $name]);
    }

    #[Test]
    public function test_admin_edit_is_opening(): void
    {
        $configRoomBedType = ConfigRoomBedType::factory()->create();

        $this->get(route('configurations.room-bed-types.edit', $configRoomBedType->id))
            ->assertSeeLivewire(RoomBedTypeForm::class)
            ->assertStatus(200);

        $component = Livewire::test(RoomBedTypeForm::class, ['configRoomBedType' => $configRoomBedType]);

        $name = $this->faker->name;

        $component->set('data', [
            'name' => $name,
        ]);

        $component->call('edit');
        $component->assertRedirect(route('configurations.room-bed-types.index'));

        $this->assertDatabaseHas('config_room_bed_types', ['name' => $name]);
    }
}
