<?php

namespace Tests\Feature\CustomAuthorizedActions;

use Illuminate\Foundation\Testing\WithFaker;
use App\Livewire\ExpediaTable;
use App\Models\ExpediaContent;
use Livewire\Livewire;

class ExpediaContentTest extends CustomAuthorizedActionsTestCase
{
    use WithFaker;

    /**
     * @test
     * @return void
     */
    public function test_expedia_table_index_is_opening(): void
    {
        $response = $this->get('/admin/expedia');

        $response->assertStatus(200);
    }

    /**
     * @test
     * @return void
     */
    public function test_expedia_table_is_rendering_as_well_as_city_with_search_name(): void
    {
//        $expedia = ExpediaContent::take(10)->get();
//
//        livewire::test(ExpediaTable::class)->assertSuccessful();
//
//        livewire::test(ExpediaTable::class)
//            ->assertCanRenderTableColumn('city');
//
//        $name = $expedia->first()->name;
//
//        livewire::test(ExpediaTable::class)
//            ->searchTable($name)
//            ->assertCanSeeTableRecords($expedia->where($this->faker->name, $name))
//            ->assertCanNotSeeTableRecords($expedia->where('name', '!=', $name));
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_filtering_by_name(): void
    {
//        $expedia = ExpediaContent::take(10)->get();
//
//        $nameToFilter = $expedia->first()->name;
//
//        livewire::test(ExpediaTable::class)
//            ->searchTable('name')
//            ->assertCanSeeTableRecords($expedia->where($this->faker->name, $nameToFilter))
//            ->assertCanNotSeeTableRecords($expedia->where('name', '!=', $nameToFilter));
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_filtering_by_city(): void
    {
//        $expedia = ExpediaContent::take(10)->get();
//
//        $cityToFilter = $expedia[0]->city;
//
//        $cityToFilter1 = $expedia[1]->city;
//
//        livewire::test(ExpediaTable::class)
//            ->searchTable('city')
//            ->assertCanSeeTableRecords($expedia->where($this->faker->city, $cityToFilter))
//            ->assertCanNotSeeTableRecords($expedia->where('city', '!=', $cityToFilter1));
    }
}
