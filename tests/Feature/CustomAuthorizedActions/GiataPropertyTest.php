<?php

namespace Tests\Feature\CustomAuthorizedActions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\WithFaker;
use App\Livewire\GiataTable;
use App\Models\GiataProperty;
use Livewire\Livewire;

class GiataPropertyTest extends CustomAuthorizedActionsTestCase
{
    use WithFaker;

    /**
     * @var Collection|GiataProperty|Model|null
     */
    private Collection|GiataProperty|Model|null $giata = null;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $giata = GiataProperty::take(10)->get();

        if (!$giata && env('APP_ENV') === 'testing') $giata = GiataProperty::factory()->count(10)->create();

        $this->giata = $giata;
    }

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
        livewire::test(GiataTable::class)->assertSuccessful();

        livewire::test(GiataTable::class)
            ->assertCanRenderTableColumn('city');

        $name = $this->giata->first()->name;

        $name1 = $this->giata[2]->name;

        livewire::test(GiataTable::class)
            ->searchTable($name)
            ->assertCanSeeTableRecords($this->giata->where($this->faker->name, $name))
            ->assertDontSee($name1);
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_searching_by_name(): void
    {
        $name = $this->giata->first()->name;

        livewire::test(GiataTable::class)
            ->searchTable($name)
            ->assertCanSeeTableRecords($this->giata->where('name', $name))
            ->assertCanNotSeeTableRecords($this->giata->where('name', '!=', $name));
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_searching_by_city(): void
    {
        $city = $this->giata->first()->city;

        livewire::test(GiataTable::class)
            ->searchTable($city)
            ->assertCanSeeTableRecords($this->giata->where('city', $city))
            ->assertCanNotSeeTableRecords($this->giata->where('city', '!=', $city));
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_searching_by_address(): void
    {
        $address = $this->giata->first()->mapper_address;

        livewire::test(GiataTable::class)
            ->searchTable($address)
            ->assertCanSeeTableRecords($this->giata->where('mapper_address', $address))
            ->assertCanNotSeeTableRecords($this->giata->where('mapper_address', '!=', $address));
    }
}
