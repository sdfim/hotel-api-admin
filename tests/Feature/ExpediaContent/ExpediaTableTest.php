<?php

namespace Tests\Feature\ExpediaContent;

use App\Livewire\ExpediaTable;
use App\Models\ExpediaContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class ExpediaTableTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testCanRenderCity(): void
    {
        $this->auth();
        $expedia = ExpediaContent::factory()->count(10)->create();
        //'can render page'
        livewire::test(ExpediaTable::class)->assertSuccessful();
        //'can render post titles'
        livewire::test(ExpediaTable::class)
            ->assertCanRenderTableColumn('city');
        //'can search posts by title'
        $name = $expedia->first()->name;

        livewire::test(ExpediaTable::class)
            ->searchTable($name)
            ->assertCanSeeTableRecords($expedia->where($this->faker->name, $name))
            ->assertCanNotSeeTableRecords($expedia->where($this->faker->name, '!=', $name));
    }

    public function testCanFilterName()
    {
        $this->auth();
        $expedia = ExpediaContent::factory()->count(10)->create();
        $nameToFilter = $expedia->first()->name;
        livewire::test(ExpediaTable::class)
            ->filterTable('name', ['name' => $nameToFilter])
            ->assertSee($nameToFilter)
            ->assertDontSee($expedia->where('name', '!=', $nameToFilter)->pluck('name')->all());
    }
    public function testCanFilterByCity()
    {
        $expedia = ExpediaContent::factory()->count(10)->create();
        $cityToFilter = $expedia[0]->city;
        $cityToFilter1 = $expedia[1]->city;
        $cityToFilter2 = $expedia[2]->city;
        livewire::test(ExpediaTable::class)
            ->filterTable('city', ['city' => $cityToFilter])
            ->assertSee($cityToFilter)
            ->assertDontSee($cityToFilter1)
            ->assertDontSee($cityToFilter2);
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
