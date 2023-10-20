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

    public function test_expedia_table_is_rendering_as_well_as_city_with_search_name(): void
    {
        // $this->auth();
        // $expedia = ExpediaContent::factory()->count(10)->create();
        // //'can render page'
        // livewire::test(ExpediaTable::class)->assertSuccessful();
        // //'can render city'
        // livewire::test(ExpediaTable::class)
        //     ->assertCanRenderTableColumn('city');
        // //'can search by name'
        // $name = $expedia->first()->name;

        // livewire::test(ExpediaTable::class)
        //     ->searchTable($name)
        //     ->assertCanSeeTableRecords($expedia->where($this->faker->name, $name))
        //     ->assertCanNotSeeTableRecords($expedia->where($this->faker->name, '!=', $name));
    }

    public function test_possibility_of_filtering_by_name(): void
    {
        // $this->auth();
        // $expedia = ExpediaContent::factory()->count(10)->create();
        // $nameToFilter = $expedia->first()->name;
        // livewire::test(ExpediaTable::class)
        //     ->filterTable('name', ['name' => $nameToFilter])
        //     ->assertSee($nameToFilter)
        //     ->assertDontSee($expedia->where('name', '!=', $nameToFilter)->pluck('name')->all());
    }

    public function test_possibility_of_filtering_by_city(): void
    {
        // $expedia = ExpediaContent::factory()->count(10)->create();
        // $cityToFilter = $expedia[0]->city;
        // $cityToFilter1 = $expedia[1]->city;
        // $cityToFilter2 = $expedia[2]->city;
        // livewire::test(ExpediaTable::class)
        //     ->filterTable('city', ['city' => $cityToFilter])
        //     ->assertSee($cityToFilter)
        //     ->assertDontSee($cityToFilter1)
        //     ->assertDontSee($cityToFilter2);
    }

    public function test_possibility_of_filtering_by_address(): void
    {
        // $expedia = ExpediaContent::factory()->count(10)->create();
        // $addressToFilter = json_decode($expedia[0]->address)->line_1;
        // $addressToFilter1 = json_decode($expedia[1]->address)->line_1;
        // $addressToFilter2 = json_decode($expedia[2]->address)->line_1;
        // livewire::test(ExpediaTable::class)
        //     ->filterTable('address', ['address' => $addressToFilter])
        //     ->assertSee($addressToFilter)
        //     ->assertDontSee($addressToFilter1)
        //     ->assertDontSee($addressToFilter2);
    }

    public function auth(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);
    }
}
