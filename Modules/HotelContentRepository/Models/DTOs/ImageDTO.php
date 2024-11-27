<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Modules\HotelContentRepository\Models\Image;

class ImageDTO
{
    public $id;
    public $image_url;
    public $tag;
    public $weight;
    public $section_id;

    public function __construct(Image $image)
    {
        $this->id = $image->id;
        $this->image_url = $image->image_url;
        $this->tag = $image->tag;
        $this->weight = $image->weight;
        $this->section_id = $image->section_id;
    }
}
