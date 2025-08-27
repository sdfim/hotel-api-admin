<?php

use App\Actions\ConfigConsortium\CreateConfigConsortium;
use App\Models\Configurations\ConfigConsortium;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('create config consortium creates record', function () {
    $data = [
        'name' => 'Test Consortium',
        'description' => 'Test description',
    ];

    $action = new CreateConfigConsortium();
    $consortium = $action->create($data);

    expect($consortium)->toBeInstanceOf(ConfigConsortium::class);
    expect(ConfigConsortium::where($data)->exists())->toBeTrue();
});

