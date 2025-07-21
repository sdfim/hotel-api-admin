<?php

use App\Models\Mapping;
use Illuminate\Foundation\Testing\WithFaker;

uses(WithFaker::class);

test('index view is rendered correctly', function () {
    Mapping::factory()->count(5)->create();

    $this->get(route('expedia.index'))
        ->assertStatus(200)
        ->assertViewIs('dashboard.expedia.index');
});
