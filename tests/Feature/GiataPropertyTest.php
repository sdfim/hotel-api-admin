<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Livewire\GiataTable;
use App\Models\GiataProperty;
use App\Models\User;
use Livewire\Livewire;


class GiataPropertyTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test
     * @return void
     */
    public function test_giata_table_index_is_opening(): void
    {
        $this->auth();

        $response = $this->get('/admin/giata');

        $response->assertStatus(200);
    }

    /**
     * @test
     * @return void
     */
    public function test_giata_table_is_rendering_as_well_as_city_with_search_name(): void
    {
        // $this->auth();
        // $giata = GiataProperty::factory()->count(10)->create();
        // //'can render page'
        // livewire::test(GiataTable::class)->assertSuccessful();
        // //'can render city'
        // livewire::test(GiataTable::class)
        //     ->assertCanRenderTableColumn('city');
        // //'can search by name'
        // $name = $giata->first()->name;
        // $name1 = $giata[2]->name;
        // livewire::test(GiataTable::class)
        //     ->searchTable($name)
        //     ->assertCanSeeTableRecords($giata->where($this->faker->name, $name))
        //     ->assertDontSee($name1);
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_filtering_by_name(): void
    {
        // $giata = GiataProperty::factory()->count(10)->create();
        // $nameToFilter = $giata[0]->name;
        // $nameToFilter1 = $giata[1]->name;
        // $nameToFilter2 = $giata[2]->name;
        // livewire::test(GiataTable::class)
        //     ->filterTable('name', ['name' => $nameToFilter])
        //     ->assertSee($nameToFilter)
        //     ->assertDontSee($nameToFilter1)
        //     ->assertDontSee($nameToFilter2);
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_filtering_by_city(): void
    {
        // $giata = GiataProperty::factory()->count(10)->create();
        // $cityToFilter = $giata[0]->city;
        // $cityToFilter1 = $giata[1]->city;
        // $cityToFilter2 = $giata[2]->city;
        // livewire::test(GiataTable::class)
        //     ->filterTable('city', ['city' => $cityToFilter])
        //     ->assertSee($cityToFilter)
        //     ->assertDontSee($cityToFilter1)
        //     ->assertDontSee($cityToFilter2);
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_filtering_by_address(): void
    {
        // $giata = GiataProperty::factory()->count(10)->create();
        // $addressToFilter = json_decode($giata[0]['address'])->AddressLine;
        // $addressToFilter1 = json_decode($giata[1]['address'])->AddressLine;
        // $addressToFilter2 = json_decode($giata[2]['address'])->AddressLine;

        // livewire::test(GiataTable::class)
        //     ->filterTable('address', ['address' => $addressToFilter])
        //     ->assertSee($addressToFilter)
        //     ->assertDontSee($addressToFilter1)
        //     ->assertDontSee($addressToFilter2);
    }

    /**
     * @return void
     */
    public function auth(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);
    }
}
