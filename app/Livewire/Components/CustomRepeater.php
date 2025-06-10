<?php

namespace App\Livewire\Components;

use Filament\Forms\Components\Repeater as BaseRepeater;

class CustomRepeater extends BaseRepeater
{
    protected string $view = 'livewire.components.custom-repeater-item';

    public function getDefaultView(): string
    {
        return 'livewire.components.custom-repeater-item';
    }
}
