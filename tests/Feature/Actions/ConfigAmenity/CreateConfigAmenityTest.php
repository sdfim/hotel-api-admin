<?php

use App\Actions\ConfigAmenity\CreateConfigAmenity;
use App\Models\Configurations\ConfigAmenity;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('create config amenity creates record', function () {
    $data = [
        'name' => 'Test Amenity',
        'description' => 'Test amenity description',
    ];

    $action = new CreateConfigAmenity();
    $amenity = $action->create($data);

    expect($amenity)->toBeInstanceOf(ConfigAmenity::class);
    expect(ConfigAmenity::where($data)->exists())->toBeTrue();
});

