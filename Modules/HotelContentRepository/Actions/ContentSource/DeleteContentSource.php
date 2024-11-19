<?php

namespace Modules\HotelContentRepository\Actions\ContentSource;

use Modules\HotelContentRepository\Events\ContentSource\ContentSourceDeleted;
use Modules\HotelContentRepository\Models\ContentSource;

class DeleteContentSource
{
    public function handle(ContentSource $contentSource)
    {
        $contentSource->delete();
        ContentSourceDeleted::dispatch($contentSource);
    }
}
