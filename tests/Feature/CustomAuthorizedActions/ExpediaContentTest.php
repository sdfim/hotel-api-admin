<?php

namespace Tests\Feature\CustomAuthorizedActions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\WithFaker;
use App\Livewire\ExpediaTable;
use App\Models\ExpediaContent;
use Livewire\Livewire;
use Illuminate\Database\Eloquent\Collection;

class ExpediaContentTest extends CustomAuthorizedActionsTestCase
{
    use WithFaker;

    /**
     * @var Model|Collection|ExpediaContent|null
     */
    private Collection|ExpediaContent|Model|null $expedia = null;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->expedia = ExpediaContent::take(10)->get();

        if (!$this->expedia->isEmpty() && env('SECOND_DB_HOST') === 'mysql') $this->expedia = ExpediaContent::factory()->count(10)->create();
    }

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
        livewire::test(ExpediaTable::class)->assertSuccessful();

        livewire::test(ExpediaTable::class)
            ->assertCanRenderTableColumn('city');

        $name = $this->expedia->first()->name;

        livewire::test(ExpediaTable::class)
            ->searchTable($name)
            ->assertCanSeeTableRecords($this->expedia->where($this->faker->name, $name))
            ->assertCanNotSeeTableRecords($this->expedia->where('name', '!=', $name));
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_filtering_by_name(): void
    {
        $nameToFilter = $this->expedia->first()->name;

        livewire::test(ExpediaTable::class)
            ->searchTable('name')
            ->assertCanSeeTableRecords($this->expedia->where($this->faker->name, $nameToFilter))
            ->assertCanNotSeeTableRecords($this->expedia->where('name', '!=', $nameToFilter));
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_filtering_by_city(): void
    {
        $cityToFilter = $this->expedia[0]->city;

        $cityToFilter1 = $this->expedia[1]->city;

        livewire::test(ExpediaTable::class)
            ->searchTable('city')
            ->assertCanSeeTableRecords($this->expedia->where($this->faker->city, $cityToFilter))
            ->assertCanNotSeeTableRecords($this->expedia->where('city', '!=', $cityToFilter1));
    }
}
