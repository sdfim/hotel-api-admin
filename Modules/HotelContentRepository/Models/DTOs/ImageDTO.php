<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Illuminate\Database\Eloquent\Collection;
use Modules\HotelContentRepository\Models\Image;

class ImageDTO
{
    public $id;
    public $image_url;
    public $tag;
    public $weight;
    public $section_id;

    public function __construct() {}

    public function transform(Collection $images)
    {
        return $images->map(function ($image) {
            return $this->transformImage($image);
        })->all();
    }

    public function transformImage(Image $image)
    {
        return [
            'id' => $image->id,
            'image_url' => $image->image_url,
            'tag' => $image->tag,
            'weight' => $image->weight,
            'alt' => $image->alt,
            'section_id' => $image->section_id,
        ];
    }
}
