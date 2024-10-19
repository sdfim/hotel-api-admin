<?php

namespace Modules\HotelContentRepository\Filament;

use Filament\Tables\Columns\TextColumn as BaseTextColumn;

class CustomTextColumn extends BaseTextColumn
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->extraAttributes(['style' => 'max-width: 200px; white-space: normal; word-wrap: break-word;']);
        $this->limit(50);
    }

    public function formatState($state): string
    {
        return preg_replace('/,/', ', ', parent::formatState($state));
    }
}
