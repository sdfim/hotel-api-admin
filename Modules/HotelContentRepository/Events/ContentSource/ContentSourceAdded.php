<?php

namespace Modules\HotelContentRepository\Events\ContentSource;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\HotelContentRepository\Models\ContentSource;

class ContentSourceAdded
{
    use Dispatchable, SerializesModels;

    public $contentSource;

    public function __construct(ContentSource $contentSource)
    {
        $this->contentSource = $contentSource;
    }
}
