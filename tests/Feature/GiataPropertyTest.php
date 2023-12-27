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
        $this->auth();

        $giata = GiataProperty::take(10)->get();

        livewire::test(GiataTable::class)->assertSuccessful();

        livewire::test(GiataTable::class)
            ->assertCanRenderTableColumn('city');

        $name = $giata->first()->name;

        $name1 = $giata[2]->name;

        livewire::test(GiataTable::class)
            ->searchTable($name)
            ->assertCanSeeTableRecords($giata->where($this->faker->name, $name))
            ->assertDontSee($name1);
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_searching_by_name(): void
    {
        $giata = GiataProperty::take(10)->get();

        $name = $giata->first()->name;

        livewire::test(GiataTable::class)
            ->searchTable($name)
            ->assertCanSeeTableRecords($giata->where('name', $name))
            ->assertCanNotSeeTableRecords($giata->where('name', '!=', $name));
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_searching_by_city(): void
    {
        $giata = GiataProperty::take(10)->get();

        $city = $giata->first()->city;

        livewire::test(GiataTable::class)
            ->searchTable($city)
            ->assertCanSeeTableRecords($giata->where('city', $city))
            ->assertCanNotSeeTableRecords($giata->where('city', '!=', $city));
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_searching_by_address(): void
    {
        $giata = GiataProperty::take(10)->get();

        $address = json_decode($giata->first()->address)->AddressLine;

        livewire::test(GiataTable::class)
            ->searchTable($address)
            ->assertCanSeeTableRecords($giata->where('address', $address))
            ->assertCanNotSeeTableRecords($giata->where('address', '!=', $address));
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
