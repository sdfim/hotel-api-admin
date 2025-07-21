<?php

namespace Modules\HotelContentRepository\Livewire\Components;

use Filament\Forms\Components\Toggle;

class CustomToggle extends Toggle
{
    protected string $view = 'livewire.components.custom-toggle';

    protected ?string $tooltipText = null;

    public function tooltip(string $text): static
    {
        $this->tooltipText = $text;

        return $this;
    }

    public function getTooltipText(): ?string
    {
        return nl2br($this->tooltipText);
    }
}
