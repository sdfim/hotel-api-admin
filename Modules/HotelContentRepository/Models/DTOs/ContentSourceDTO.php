<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Illuminate\Database\Eloquent\Collection;
use Modules\HotelContentRepository\Models\ContentSource;

class ContentSourceDTO
{
    public $id;
    public $name;

    public function __construct() {}

    public function transform(Collection $contentSources)
    {
        return $contentSources->map(function ($contentSource) {
            return $this->transformContentSource($contentSource);
        })->all();
    }

    public function transformContentSource(ContentSource $contentSource)
    {
        return [
            'id' => $contentSource->id,
            'name' => $contentSource->name,
        ];
    }
}
