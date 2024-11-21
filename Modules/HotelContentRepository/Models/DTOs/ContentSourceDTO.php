<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Modules\HotelContentRepository\Models\ContentSource;

class ContentSourceDTO
{
    public $id;
    public $name;

    public function __construct(ContentSource $contentSource)
    {
        $this->id = $contentSource->id;
        $this->name = $contentSource->name;
    }
}
