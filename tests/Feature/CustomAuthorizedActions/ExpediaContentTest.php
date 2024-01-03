<?php

namespace Tests\Feature\CustomAuthorizedActions;

use Illuminate\Database\Eloquent\Model;
use App\Livewire\ExpediaTable;
use App\Models\ExpediaContent;
use Livewire\Livewire;
use Illuminate\Database\Eloquent\Collection;

class ExpediaContentTest extends CustomAuthorizedActionsTestCase
{
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

        if ($this->expedia->isEmpty() && env('SECOND_DB_HOST') === 'mysql') $this->expedia = ExpediaContent::factory()->count(10)->create();
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

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_searching_by_property_id(): void
    {
        $propertyId = $this->expedia->first()->property_id;

        livewire::test(ExpediaTable::class)
            ->searchTableColumns(['property_id' => $propertyId])
            ->assertCanSeeTableRecords($this->expedia->where('property_id', $propertyId))
            ->assertCanNotSeeTableRecords($this->expedia->where('property_id', '!=', $propertyId));
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_searching_by_name(): void
    {
        $name = $this->expedia[9]->name;

        livewire::test(ExpediaTable::class)
            ->searchTableColumns(['name' => $name])
            ->assertCanSeeTableRecords($this->expedia->where('name', $name))
            ->assertCanNotSeeTableRecords($this->expedia->where('name', '!=', $name));
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_searching_by_rating(): void
    {
        $rating = $this->expedia[rand(0, 9)]->rating;

        livewire::test(ExpediaTable::class)
            ->searchTableColumns(['rating' => $rating])
            ->assertCanSeeTableRecords($this->expedia->where('rating', $rating))
            ->assertCanNotSeeTableRecords($this->expedia->where('rating', '!=', $rating));
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_searching_by_city(): void
    {
        $city = $this->expedia[rand(0, 9)]->city;

        livewire::test(ExpediaTable::class)
            ->searchTableColumns(['city' => $city])
            ->assertCanSeeTableRecords($this->expedia->where('city', $city))
            ->assertCanNotSeeTableRecords($this->expedia->where('city', '!=', $city));
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_searching_by_latitude(): void
    {
        $latitude = $this->expedia[rand(0, 9)]->latitude;

        livewire::test(ExpediaTable::class)
            ->searchTableColumns(['latitude' => $latitude])
            ->assertCanSeeTableRecords($this->expedia->where('latitude', $latitude))
            ->assertCanNotSeeTableRecords($this->expedia->where('latitude', '!=', $latitude));
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_searching_by_longitude(): void
    {
        $longitude = $this->expedia[rand(0, 9)]->longitude;

        livewire::test(ExpediaTable::class)
            ->searchTableColumns(['longitude' => $longitude])
            ->assertCanSeeTableRecords($this->expedia->where('longitude', $longitude))
            ->assertCanNotSeeTableRecords($this->expedia->where('longitude', '!=', $longitude));
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_searching_by_phone(): void
    {
        $phone = $this->expedia->first()->phone;

        livewire::test(ExpediaTable::class)
            ->searchTableColumns(['phone' => $phone])
            ->assertCanSeeTableRecords($this->expedia->where('phone', $phone))
            ->assertCanNotSeeTableRecords($this->expedia->where('phone', '!=', $phone));
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_searching_by_address(): void
    {
        $address = $this->expedia[rand(0, 9)]->address['line_1'];

        livewire::test(ExpediaTable::class)
            ->searchTableColumns(['address' => $address])
            ->assertCanSeeTableRecords($this->expedia->filter(function ($item) use ($address) {
                return $item->address['line_1'] === $address;
            }))
            ->assertCanNotSeeTableRecords($this->expedia->filter(function ($item) use ($address) {
                return $item->address['line_1'] !== $address;
            }));
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_searching_by_is_active(): void
    {
        $isActive = $this->expedia[rand(0, 9)]->is_active;

        $this->expedia[rand(0, 9)]->is_active = !$isActive;

        livewire::test(ExpediaTable::class)
            ->searchTableColumns(['is_active' => $isActive])
            ->assertCanSeeTableRecords($this->expedia->where('is_active', $isActive))
            ->assertCanNotSeeTableRecords($this->expedia->where('is_active', '!=', $isActive) ?? []);
    }
}
