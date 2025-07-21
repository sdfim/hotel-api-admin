<?php

use App\Livewire\PropertiesTable;
use App\Models\Property;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->giata = Property::factory()->count(3)->create();
});

test('giata table index is opening', function () {
    $this->get('/admin/properties')
        ->assertStatus(200);
});

test('giata table is rendering with its columns', function () {
    Livewire::test(PropertiesTable::class)->assertSuccessful();

    Livewire::test(PropertiesTable::class)
        ->assertCanRenderTableColumn('code')
        ->assertCanRenderTableColumn('name')
        ->assertCanRenderTableColumn('city')
        ->assertCanRenderTableColumn('locale')
        ->assertCanRenderTableColumn('latitude')
        ->assertCanRenderTableColumn('longitude')
        ->assertCanRenderTableColumn('mapper_address')
        ->assertCanRenderTableColumn('mapper_phone_number')
        ->assertCanRenderTableColumn('source');
});

test('possibility of searching by code', function () {
    $code = $this->giata->first()->code;

    Livewire::test(PropertiesTable::class)
        ->searchTableColumns(['code' => $code])
        ->assertCanSeeTableRecords($this->giata->where('code', $code))
        ->assertCanNotSeeTableRecords($this->giata->where('code', '!=', $code));
});

test('possibility of searching by city', function () {
    $city = $this->giata->first()->city;

    Livewire::test(PropertiesTable::class)
        ->searchTableColumns(['city' => $city])
        ->assertCanSeeTableRecords($this->giata->where('city', $city))
        ->assertCanNotSeeTableRecords($this->giata->where('city', '!=', $city));
});

test('possibility of searching by locale', function () {
    $locale = $this->giata->first()->locale;

    Livewire::test(PropertiesTable::class)
        ->searchTableColumns(['locale' => $locale])
        ->assertSee($this->giata->where('locale', $locale)->first()->name)
        ->assertDontSee($this->giata->where('locale', '!=', $locale)->first()->name);
});

test('possibility of searching by latitude', function () {
    $latitude = $this->giata->first()->latitude;

    Livewire::test(PropertiesTable::class)
        ->searchTableColumns(['latitude' => $latitude])
        ->assertCanSeeTableRecords($this->giata->where('latitude', $latitude))
        ->assertCanNotSeeTableRecords($this->giata->where('latitude', '!=', $latitude));
});

test('possibility of searching by longitude', function () {
    $longitude = $this->giata->first()->longitude;

    Livewire::test(PropertiesTable::class)
        ->searchTableColumns(['longitude' => $longitude])
        ->assertCanSeeTableRecords($this->giata->where('longitude', $longitude))
        ->assertCanNotSeeTableRecords($this->giata->where('longitude', '!=', $longitude));
});

test('possibility of searching by address', function () {
    $mapperAddress = $this->giata->first()->mapper_address;

    Livewire::test(PropertiesTable::class)
        ->searchTableColumns(['mapper_address' => $mapperAddress])
        ->assertCanSeeTableRecords($this->giata->where('mapper_address', $mapperAddress))
        ->assertCanNotSeeTableRecords($this->giata->where('mapper_address', '!=', $mapperAddress));
});

test('possibility of searching by phone', function () {
    $mapperPhoneNumber = $this->giata->first()->mapper_phone_number;

    Livewire::test(PropertiesTable::class)
        ->searchTableColumns(['mapper_phone_number' => $mapperPhoneNumber])
        ->assertCanSeeTableRecords($this->giata->where('mapper_phone_number', $mapperPhoneNumber))
        ->assertCanNotSeeTableRecords($this->giata->where('mapper_phone_number', '!=', $mapperPhoneNumber));
});
