<?php

namespace Tests\Feature\GiataProperty;

use App\Livewire\ExpediaTable;
use App\Livewire\GiataTable;
use App\Models\GiataProperty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class GiataTableTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testCanRenderCity(): void
    {
        $this->auth();
        livewire::test(GiataTable::class)
            ->assertCanRenderTableColumn('city');
    }

    public function testCanFilterByName()
    {
        $giata = GiataTable::factory()->count(10)->create();
        $nameToFilter = $giata[0]->name;
        $nameToFilter1 = $giata[1]->name;
        $nameToFilter2 = $giata[2]->name;
        livewire::test(GiataTable::class)
            ->filterTable('name', ['name' => $nameToFilter])
            ->assertSee($nameToFilter)
            ->assertDontSee($nameToFilter1)
            ->assertDontSee($nameToFilter2);
    }

    public function auth()
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
    }
}
