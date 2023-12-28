<?php

namespace Tests\Feature\CustomAuthorizedActions;

use Illuminate\Foundation\Testing\WithFaker;
use App\Livewire\GiataTable;
use App\Models\GiataProperty;
use Livewire\Livewire;

class GiataPropertyTest extends CustomAuthorizedActionsTestCase
{
    use WithFaker;

    /**
     * @test
     * @return void
     */
    public function test_giata_table_index_is_opening(): void
    {
        $response = $this->get('/admin/giata');

        $response->assertStatus(200);
    }

    /**
     * @test
     * @return void
     */
    public function test_giata_table_is_rendering_as_well_as_city_with_search_name(): void
    {
//        $giata = GiataProperty::take(10)->get();
//
//        livewire::test(GiataTable::class)->assertSuccessful();
//
//        livewire::test(GiataTable::class)
//            ->assertCanRenderTableColumn('city');
//
//        $name = $giata->first()->name;
//
//        $name1 = $giata[2]->name;
//
//        livewire::test(GiataTable::class)
//            ->searchTable($name)
//            ->assertCanSeeTableRecords($giata->where($this->faker->name, $name))
//            ->assertDontSee($name1);
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_searching_by_name(): void
    {
//        $giata = GiataProperty::take(10)->get();
//
//        $name = $giata->first()->name;
//
//        livewire::test(GiataTable::class)
//            ->searchTable($name)
//            ->assertCanSeeTableRecords($giata->where('name', $name))
//            ->assertCanNotSeeTableRecords($giata->where('name', '!=', $name));
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_searching_by_city(): void
    {
//        $giata = GiataProperty::take(10)->get();
//
//        $city = $giata->first()->city;
//
//        livewire::test(GiataTable::class)
//            ->searchTable($city)
//            ->assertCanSeeTableRecords($giata->where('city', $city))
//            ->assertCanNotSeeTableRecords($giata->where('city', '!=', $city));
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_searching_by_address(): void
    {
//        $giata = GiataProperty::take(10)->get();
//
//        $address = json_decode($giata->first()->address)->AddressLine;
//
//        livewire::test(GiataTable::class)
//            ->searchTable($address)
//            ->assertCanSeeTableRecords($giata->where('address', $address))
//            ->assertCanNotSeeTableRecords($giata->where('address', '!=', $address));
    }
}
