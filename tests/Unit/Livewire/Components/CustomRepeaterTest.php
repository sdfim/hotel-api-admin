<?php

use App\Livewire\Components\CustomRepeater;

test('custom repeater returns correct default view', function () {
    $repeater = new CustomRepeater('test');
    expect($repeater->getDefaultView())->toBe('livewire.components.custom-repeater-item');
});

