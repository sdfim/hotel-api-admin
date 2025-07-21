<?php

use App\Livewire\ExpediaTable;
use App\Models\ExpediaContent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Livewire\Livewire;

beforeEach(function () {
    $this->expedia = ExpediaContent::factory()->count(10)->create()->sortByDesc('rating');
});

test('expedia table index is opening', function () {
    $this->get('/admin/expedia')
        ->assertStatus(200);
});

test('expedia table is rendering with its columns', function () {
    Livewire::test(ExpediaTable::class)->assertSuccessful();

    Livewire::test(ExpediaTable::class)
        ->assertCanRenderTableColumn('property_id')
        ->assertCanRenderTableColumn('name')
        ->assertCanRenderTableColumn('rating')
        ->assertCanRenderTableColumn('city')
        ->assertCanRenderTableColumn('latitude')
        ->assertCanRenderTableColumn('longitude')
        ->assertCanRenderTableColumn('phone')
        ->assertCanRenderTableColumn('address')
        ->assertCanRenderTableColumn('is_active');
});

test('possibility of searching by property id', function () {
    $propertyId = $this->expedia->first()->property_id;

    Livewire::test(ExpediaTable::class)
        ->searchTableColumns(['property_id' => $propertyId])
        ->assertCanSeeTableRecords($this->expedia->where('property_id', $propertyId))
        ->assertCanNotSeeTableRecords($this->expedia->where('property_id', '!=', $propertyId));
});

test('possibility of searching by name', function () {
    $name = $this->expedia[9]->name;

    Livewire::test(ExpediaTable::class)
        ->searchTableColumns(['name' => $name])
        ->assertCanSeeTableRecords($this->expedia->where('name', $name))
        ->assertCanNotSeeTableRecords($this->expedia->where('name', '!=', $name));
});

test('possibility of searching by rating', function () {
    $rating = $this->expedia[rand(0, 9)]->rating;

    Livewire::test(ExpediaTable::class)
        ->searchTableColumns(['rating' => $rating])
        ->sortTable('rating', 'desc')
        ->assertCanSeeTableRecords($this->expedia->where('rating', $rating))
        ->assertCanNotSeeTableRecords($this->expedia->where('rating', '!=', $rating));
});

test('possibility of searching by city', function () {
    $city = $this->expedia[9]->city;

    Livewire::test(ExpediaTable::class)
        ->searchTableColumns(['city' => $city])
        ->assertCanSeeTableRecords($this->expedia->where('city', $city))
        ->assertCanNotSeeTableRecords($this->expedia->where('city', '!=', $city));
});

test('possibility of searching by latitude', function () {
    $latitude = $this->expedia[rand(0, 9)]->latitude;

    Livewire::test(ExpediaTable::class)
        ->searchTableColumns(['latitude' => $latitude])
        ->assertCanSeeTableRecords($this->expedia->where('latitude', $latitude))
        ->assertCanNotSeeTableRecords($this->expedia->where('latitude', '!=', $latitude));
});

test('possibility of searching by longitude', function () {
    $longitude = $this->expedia[rand(0, 9)]->longitude;

    Livewire::test(ExpediaTable::class)
        ->searchTableColumns(['longitude' => $longitude])
        ->assertCanSeeTableRecords($this->expedia->where('longitude', $longitude))
        ->assertCanNotSeeTableRecords($this->expedia->where('longitude', '!=', $longitude));
});

test('possibility of searching by phone', function () {
    $phone = $this->expedia->first()->phone;

    Livewire::test(ExpediaTable::class)
        ->searchTableColumns(['phone' => $phone])
        ->assertCanSeeTableRecords($this->expedia->where('phone', $phone))
        ->assertCanNotSeeTableRecords($this->expedia->where('phone', '!=', $phone));
});

test('possibility of searching by address', function () {
    $address = json_decode($this->expedia->random()->address, true)['line_1'];

    Livewire::test(ExpediaTable::class)
        ->searchTableColumns(['address' => $address])
        ->assertCanSeeTableRecords($this->expedia->filter(function ($item) use ($address) {
            return json_decode($item->address, true)['line_1'] === $address;
        }))
        ->assertCanNotSeeTableRecords($this->expedia->filter(function ($item) use ($address) {
            return json_decode($item->address, true)['line_1'] !== $address;
        }));
});

test('possibility of searching by is active', function () {
    $isActive = $this->expedia->random()->is_active;

    Livewire::test(ExpediaTable::class)
        ->searchTableColumns(['is_active' => $isActive])
        ->assertCanSeeTableRecords($this->expedia->where('is_active', $isActive)->pluck('id'))
        ->assertCanNotSeeTableRecords($this->expedia->where('is_active', '!=', $isActive));
});
