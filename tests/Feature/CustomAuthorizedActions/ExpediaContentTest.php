<?php

namespace Tests\Feature\CustomAuthorizedActions;

use PHPUnit\Framework\Attributes\Test;
use App\Livewire\ExpediaTable;
use App\Models\ExpediaContent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Livewire\Livewire;

class ExpediaContentTest extends CustomAuthorizedActionsTestCase
{
    private Collection|ExpediaContent|Model|null $expedia = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->expedia = ExpediaContent::factory()->count(10)->create()->sortByDesc('rating');
    }

    #[Test]
    public function test_expedia_table_index_is_opening(): void
    {
        $response = $this->get('/admin/expedia');

        $response->assertStatus(200);
    }

    #[Test]
    public function test_expedia_table_is_rendering_with_its_columns(): void
    {
        livewire::test(ExpediaTable::class)->assertSuccessful();

        livewire::test(ExpediaTable::class)
            ->assertCanRenderTableColumn('property_id')
            ->assertCanRenderTableColumn('name')
            ->assertCanRenderTableColumn('rating')
            ->assertCanRenderTableColumn('city')
            ->assertCanRenderTableColumn('latitude')
            ->assertCanRenderTableColumn('longitude')
            ->assertCanRenderTableColumn('phone')
            ->assertCanRenderTableColumn('address')
            ->assertCanRenderTableColumn('is_active');
    }

    #[Test]
    public function test_possibility_of_searching_by_property_id(): void
    {
        $propertyId = $this->expedia->first()->property_id;

        livewire::test(ExpediaTable::class)
            ->searchTableColumns(['property_id' => $propertyId])
            ->assertCanSeeTableRecords($this->expedia->where('property_id', $propertyId))
            ->assertCanNotSeeTableRecords($this->expedia->where('property_id', '!=', $propertyId));
    }

    #[Test]
    public function test_possibility_of_searching_by_name(): void
    {
        $name = $this->expedia[9]->name;

        livewire::test(ExpediaTable::class)
            ->searchTableColumns(['name' => $name])
            ->assertCanSeeTableRecords($this->expedia->where('name', $name))
            ->assertCanNotSeeTableRecords($this->expedia->where('name', '!=', $name));
    }

    #[Test]
    public function test_possibility_of_searching_by_city(): void
    {
        $city = $this->expedia[9]->city;

        livewire::test(ExpediaTable::class)
            ->searchTableColumns(['city' => $city])
            ->assertCanSeeTableRecords($this->expedia->where('city', $city))
            ->assertCanNotSeeTableRecords($this->expedia->where('city', '!=', $city));
    }

    #[Test]
    public function test_possibility_of_searching_by_latitude(): void
    {
        $latitude = $this->expedia[rand(0, 9)]->latitude;

        livewire::test(ExpediaTable::class)
            ->searchTableColumns(['latitude' => $latitude])
            ->assertCanSeeTableRecords($this->expedia->where('latitude', $latitude))
            ->assertCanNotSeeTableRecords($this->expedia->where('latitude', '!=', $latitude));
    }

    #[Test]
    public function test_possibility_of_searching_by_longitude(): void
    {
        $longitude = $this->expedia[rand(0, 9)]->longitude;

        livewire::test(ExpediaTable::class)
            ->searchTableColumns(['longitude' => $longitude])
            ->assertCanSeeTableRecords($this->expedia->where('longitude', $longitude))
            ->assertCanNotSeeTableRecords($this->expedia->where('longitude', '!=', $longitude));
    }

    #[Test]
    public function test_possibility_of_searching_by_phone(): void
    {
        $phone = $this->expedia->first()->phone;

        livewire::test(ExpediaTable::class)
            ->searchTableColumns(['phone' => $phone])
            ->assertCanSeeTableRecords($this->expedia->where('phone', $phone))
            ->assertCanNotSeeTableRecords($this->expedia->where('phone', '!=', $phone));
    }

    #[Test]
    public function test_possibility_of_searching_by_address(): void
    {
        $address = json_decode($this->expedia->random()->address, true)['line_1'];

        livewire::test(ExpediaTable::class)
            ->searchTableColumns(['address' => $address])
            ->assertCanSeeTableRecords($this->expedia->filter(function ($item) use ($address) {
                return json_decode($item->address, true)['line_1'] === $address;
            }))
            ->assertCanNotSeeTableRecords($this->expedia->filter(function ($item) use ($address) {
                return json_decode($item->address, true)['line_1'] !== $address;
            }));
    }

    #[Test]
    public function test_possibility_of_searching_by_is_active(): void
    {
        $isActive = $this->expedia->random()->is_active;

        livewire::test(ExpediaTable::class)
            ->searchTableColumns(['is_active' => $isActive])
            ->assertCanSeeTableRecords($this->expedia->where('is_active', $isActive)->pluck('id'))
            ->assertCanNotSeeTableRecords($this->expedia->where('is_active', '!=', $isActive));
    }
}
