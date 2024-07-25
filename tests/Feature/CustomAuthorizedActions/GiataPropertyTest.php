<?php

namespace Tests\Feature\CustomAuthorizedActions;

use App\Livewire\GiataTable;
use App\Models\GiataProperty;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Livewire\Livewire;

class GiataPropertyTest extends CustomAuthorizedActionsTestCase
{
    private Collection|GiataProperty|Model|null $giata = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->giata = GiataProperty::factory()->count(10)->create();
    }

    /**
     * @test
     */
    public function test_giata_table_index_is_opening(): void
    {
        $response = $this->get('/admin/giata');

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function test_giata_table_is_rendering_with_its_columns(): void
    {
        livewire::test(GiataTable::class)->assertSuccessful();

        livewire::test(GiataTable::class)
            ->assertCanRenderTableColumn('code')
            ->assertCanRenderTableColumn('name')
            ->assertCanRenderTableColumn('city')
            ->assertCanRenderTableColumn('city_id')
            ->assertCanRenderTableColumn('locale')
            ->assertCanRenderTableColumn('latitude')
            ->assertCanRenderTableColumn('longitude')
            ->assertCanRenderTableColumn('mapper_address')
            ->assertCanRenderTableColumn('mapper_phone_number');
    }

    /**
     * @test
     */
    public function test_possibility_of_searching_by_code(): void
    {
        $code = $this->giata->first()->code;

        livewire::test(GiataTable::class)
            ->searchTableColumns(['code' => $code])
            ->assertCanSeeTableRecords($this->giata->where('code', $code))
            ->assertCanNotSeeTableRecords($this->giata->where('code', '!=', $code));
    }

    /**
     * @test
     */
    public function test_possibility_of_searching_by_name(): void
    {
        $name = $this->giata->first()->name;

        livewire::test(GiataTable::class)
            ->searchTableColumns(['name' => $name])
            ->assertCanSeeTableRecords($this->giata->where('name', $name))
            ->assertCanNotSeeTableRecords($this->giata->where('name', '!=', $name));
    }

    /**
     * @test
     */
    public function test_possibility_of_searching_by_city(): void
    {
        $city = $this->giata->first()->city;

        livewire::test(GiataTable::class)
            ->searchTableColumns(['city' => $city])
            ->assertCanSeeTableRecords($this->giata->where('city', $city))
            ->assertCanNotSeeTableRecords($this->giata->where('city', '!=', $city));
    }

    /**
     * @test
     */
    public function test_possibility_of_searching_by_city_id(): void
    {
        $city_id = $this->giata->first()->city_id;

        livewire::test(GiataTable::class)
            ->searchTableColumns(['city_id' => $city_id])
            ->assertCanSeeTableRecords($this->giata->where('city_id', $city_id))
            ->assertCanNotSeeTableRecords($this->giata->where('city_id', '!=', $city_id));
    }

    /**
     * @test
     */
    public function test_possibility_of_searching_by_locale(): void
    {
        $locale = $this->giata->first()->locale;

        livewire::test(GiataTable::class)
            ->searchTableColumns(['locale' => $locale])
            ->assertCanSeeTableRecords($this->giata->where('locale', $locale))
            ->assertCanNotSeeTableRecords($this->giata->where('locale', '!=', $locale));
    }

    /**
     * @test
     */
    public function test_possibility_of_searching_by_latitude(): void
    {
        $latitude = $this->giata->first()->latitude;

        livewire::test(GiataTable::class)
            ->searchTableColumns(['latitude' => $latitude])
            ->assertCanSeeTableRecords($this->giata->where('latitude', $latitude))
            ->assertCanNotSeeTableRecords($this->giata->where('latitude', '!=', $latitude));
    }

    /**
     * @test
     */
    public function test_possibility_of_searching_by_longitude(): void
    {
        $longitude = $this->giata->first()->longitude;

        livewire::test(GiataTable::class)
            ->searchTableColumns(['longitude' => $longitude])
            ->assertCanSeeTableRecords($this->giata->where('longitude', $longitude))
            ->assertCanNotSeeTableRecords($this->giata->where('longitude', '!=', $longitude));
    }

    /**
     * @test
     */
    public function test_possibility_of_searching_by_address(): void
    {
        $mapperAddress = $this->giata->first()->mapper_address;

        livewire::test(GiataTable::class)
            ->searchTableColumns(['mapper_address' => $mapperAddress])
            ->assertCanSeeTableRecords($this->giata->where('mapper_address', $mapperAddress))
            ->assertCanNotSeeTableRecords($this->giata->where('mapper_address', '!=', $mapperAddress));
    }

    /**
     * @test
     */
    public function test_possibility_of_searching_by_phone(): void
    {
        $mapperPhoneNumber = $this->giata->first()->mapper_phone_number;

        livewire::test(GiataTable::class)
            ->searchTableColumns(['mapper_phone_number' => $mapperPhoneNumber])
            ->assertCanSeeTableRecords($this->giata->where('mapper_phone_number', $mapperPhoneNumber))
            ->assertCanNotSeeTableRecords($this->giata->where('mapper_phone_number', '!=', $mapperPhoneNumber));
    }
}
