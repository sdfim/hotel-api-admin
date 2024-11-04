<?php

namespace Tests\Feature\CustomAuthorizedActions;

use App\Livewire\PropertiesTable;
use App\Models\Property;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;

class PropertyTest extends CustomAuthorizedActionsTestCase
{
    use RefreshDatabase;

    private Collection|Property|Model|null $giata = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->giata = Property::factory()->count(10)->create();
    }

    #[Test]
    public function test_giata_table_index_is_opening(): void
    {
        $response = $this->get('/admin/properties');

        $response->assertStatus(200);
    }

    #[Test]
    public function test_giata_table_is_rendering_with_its_columns(): void
    {
        livewire::test(PropertiesTable::class)->assertSuccessful();

        livewire::test(PropertiesTable::class)
            ->assertCanRenderTableColumn('code')
            ->assertCanRenderTableColumn('name')
            ->assertCanRenderTableColumn('city')
            ->assertCanRenderTableColumn('locale')
            ->assertCanRenderTableColumn('latitude')
            ->assertCanRenderTableColumn('longitude')
            ->assertCanRenderTableColumn('mapper_address')
            ->assertCanRenderTableColumn('mapper_phone_number')
            ->assertCanRenderTableColumn('source');
    }

    #[Test]
    public function test_possibility_of_searching_by_code(): void
    {
        $code = $this->giata->first()->code;

        livewire::test(PropertiesTable::class)
            ->searchTableColumns(['code' => $code])
            ->assertCanSeeTableRecords($this->giata->where('code', $code))
            ->assertCanNotSeeTableRecords($this->giata->where('code', '!=', $code));
    }

    #[Test]
    public function test_possibility_of_searching_by_name(): void
    {
        $name = $this->giata->first()->name;

        livewire::test(PropertiesTable::class)
            ->searchTableColumns(['name' => $name])
            ->assertCanSeeTableRecords($this->giata->where('name', $name))
            ->assertCanNotSeeTableRecords($this->giata->where('name', '!=', $name));
    }

    #[Test]
    public function test_possibility_of_searching_by_city(): void
    {
        $city = $this->giata->first()->city;

        livewire::test(PropertiesTable::class)
            ->searchTableColumns(['city' => $city])
            ->assertCanSeeTableRecords($this->giata->where('city', $city))
            ->assertCanNotSeeTableRecords($this->giata->where('city', '!=', $city));
    }

    #[Test]
    public function test_possibility_of_searching_by_locale(): void
    {
        $locale = $this->giata->first()->locale;

        livewire::test(PropertiesTable::class)
            ->searchTableColumns(['locale' => $locale])
            ->assertCanSeeTableRecords($this->giata->where('locale', $locale))
            ->assertCanNotSeeTableRecords($this->giata->where('locale', '!=', $locale));
    }

    #[Test]
    public function test_possibility_of_searching_by_latitude(): void
    {
        $latitude = $this->giata->first()->latitude;

        livewire::test(PropertiesTable::class)
            ->searchTableColumns(['latitude' => $latitude])
            ->assertCanSeeTableRecords($this->giata->where('latitude', $latitude))
            ->assertCanNotSeeTableRecords($this->giata->where('latitude', '!=', $latitude));
    }

    #[Test]
    public function test_possibility_of_searching_by_longitude(): void
    {
        $longitude = $this->giata->first()->longitude;

        livewire::test(PropertiesTable::class)
            ->searchTableColumns(['longitude' => $longitude])
            ->assertCanSeeTableRecords($this->giata->where('longitude', $longitude))
            ->assertCanNotSeeTableRecords($this->giata->where('longitude', '!=', $longitude));
    }

    #[Test]
    public function test_possibility_of_searching_by_address(): void
    {
        $mapperAddress = $this->giata->first()->mapper_address;

        livewire::test(PropertiesTable::class)
            ->searchTableColumns(['mapper_address' => $mapperAddress])
            ->assertCanSeeTableRecords($this->giata->where('mapper_address', $mapperAddress))
            ->assertCanNotSeeTableRecords($this->giata->where('mapper_address', '!=', $mapperAddress));
    }

    #[Test]
    public function test_possibility_of_searching_by_phone(): void
    {
        $mapperPhoneNumber = $this->giata->first()->mapper_phone_number;

        livewire::test(PropertiesTable::class)
            ->searchTableColumns(['mapper_phone_number' => $mapperPhoneNumber])
            ->assertCanSeeTableRecords($this->giata->where('mapper_phone_number', $mapperPhoneNumber))
            ->assertCanNotSeeTableRecords($this->giata->where('mapper_phone_number', '!=', $mapperPhoneNumber));
    }
}
