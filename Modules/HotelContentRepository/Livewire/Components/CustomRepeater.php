<?php

namespace Modules\HotelContentRepository\Livewire\Components;

use Filament\Forms\Components\Repeater as BaseRepeater;

class CustomRepeater extends BaseRepeater
{
    public function getDefaultView(): string
    {
        return 'livewire.components.custom-cr-repeater-item';
    }
}
