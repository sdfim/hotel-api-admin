<?php

use App\Livewire\PermissionsTable;
use App\Models\Permission;
use Livewire\Livewire;

test('permissions index is opening and rendering correctly', function () {
    Permission::factory(10)->create();
    $this->get(route('permissions.index'))
        ->assertSeeLivewire(PermissionsTable::class)
        ->assertStatus(200);

    $component = Livewire::test(PermissionsTable::class);

    $permissions = Permission::limit(10)->get();
    foreach ($permissions as $permission) {
        $component->assertSee([$permission->name, $permission->slug]);
    }
});
