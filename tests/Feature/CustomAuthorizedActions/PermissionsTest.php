<?php

namespace Tests\Feature\CustomAuthorizedActions;

use App\Livewire\PermissionsTable;
use App\Models\Permission;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;

class PermissionsTest extends CustomAuthorizedActionsTestCase
{
    #[Test]
    public function test_example(): void
    {
        Permission::factory(10)->create();
        $this->get(route('permissions.index'))
            ->assertSeeLivewire(PermissionsTable::class)
            ->assertStatus(200);

        $component = Livewire::test(PermissionsTable::class);

        $permissions = Permission::limit(10)->get();
        foreach ($permissions as $permission) {
            $component->assertSee([$permission->name, $permission->slug]);
        }
    }
}
