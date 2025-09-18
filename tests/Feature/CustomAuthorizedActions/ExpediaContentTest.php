<?php

use App\Livewire\ExpediaTable;
use App\Models\ExpediaContent;
use Illuminate\Support\Collection;
use Livewire\Livewire;

beforeEach(function () {
    ExpediaContent::factory()->count(10)->create();
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
        ->assertCanRenderTableColumn('address');
});
