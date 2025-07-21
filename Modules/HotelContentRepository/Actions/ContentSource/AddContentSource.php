<?php

namespace Modules\HotelContentRepository\Actions\ContentSource;

use Modules\HotelContentRepository\API\Requests\ContentSourceRequest;
use Modules\HotelContentRepository\Events\ContentSource\ContentSourceAdded;
use Modules\HotelContentRepository\Models\ContentSource;

class AddContentSource
{
    public function handle(ContentSourceRequest $request)
    {
        $contentSource = ContentSource::create($request->validated());
        ContentSourceAdded::dispatch($contentSource);

        return $contentSource;
    }
}
