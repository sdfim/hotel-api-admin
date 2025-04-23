<?php

namespace Modules\HotelContentRepository\Actions\ContentSource;

use Modules\HotelContentRepository\API\Requests\ContentSourceRequest;
use Modules\HotelContentRepository\Events\ContentSource\ContentSourceEdited;
use Modules\HotelContentRepository\Models\ContentSource;

class EditContentSource
{
    public function handle(ContentSource $contentSource, ContentSourceRequest $request)
    {
        $contentSource->update($request->validated());
        ContentSourceEdited::dispatch($contentSource);
        return $contentSource;
    }
}
