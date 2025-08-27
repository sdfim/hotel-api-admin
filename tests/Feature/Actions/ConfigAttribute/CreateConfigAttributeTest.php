<?php

use App\Actions\ConfigAttribute\CreateConfigAttribute;
use App\Models\Configurations\ConfigAttribute;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('create config attribute creates record', function () {
    $data = [
        'name' => 'Test Attribute',
        'default_value' => 'Default',
    ];

    $action = new CreateConfigAttribute();
    $attribute = $action->create($data);

    expect($attribute)->toBeInstanceOf(ConfigAttribute::class);
    expect(ConfigAttribute::where($data)->exists())->toBeTrue();
});

