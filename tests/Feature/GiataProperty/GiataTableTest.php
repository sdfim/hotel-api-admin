<?php

namespace Tests\Feature\GiataProperty;

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

    public function testCanRenderPageAndRenderCityAndSearchName(): void
    {
        $this->auth();
        $giata = GiataProperty::factory()->count(10)->create();
        //'can render page'
        livewire::test(GiataTable::class)->assertSuccessful();
        //'can render city'
        livewire::test(GiataTable::class)
            ->assertCanRenderTableColumn('city');
        //'can search by name'
        $name = $giata->first()->name;
        $name1 = $giata[2]->name;
        livewire::test(GiataTable::class)
            ->searchTable($name)
            ->assertCanSeeTableRecords($giata->where($this->faker->name, $name))
            ->assertDontSee($name1);
    }

    public function testCanFilterByName()
    {
        $giata = GiataProperty::factory()->count(10)->create();
        $nameToFilter = $giata[0]->name;
        $nameToFilter1 = $giata[1]->name;
        $nameToFilter2 = $giata[2]->name;
        livewire::test(GiataTable::class)
            ->filterTable('name', ['name' => $nameToFilter])
            ->assertSee($nameToFilter)
            ->assertDontSee($nameToFilter1)
            ->assertDontSee($nameToFilter2);
    }

    public function testCanFilterByCity()
    {
        $giata = GiataProperty::factory()->count(10)->create();
        $cityToFilter = $giata[0]->city;
        $cityToFilter1 = $giata[1]->city;
        $cityToFilter2 = $giata[2]->city;
        livewire::test(GiataTable::class)
            ->filterTable('city', ['city' => $cityToFilter])
            ->assertSee($cityToFilter)
            ->assertDontSee($cityToFilter1)
            ->assertDontSee($cityToFilter2);
    }

    public function testCanFilterByAddress()
    {
        $giata = GiataProperty::factory()->count(10)->create();
        $addressToFilter = json_decode($giata[0]['address'])->AddressLine;
        $addressToFilter1 = json_decode($giata[1]['address'])->AddressLine;
        $addressToFilter2 = json_decode($giata[2]['address'])->AddressLine;

        livewire::test(GiataTable::class)
            ->filterTable('address', ['address' => $addressToFilter])
            ->assertSee($addressToFilter)
            ->assertDontSee($addressToFilter1)
            ->assertDontSee($addressToFilter2);
    }

    public function auth()
    {
        $user = User::factory()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);
    }
}
