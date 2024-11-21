<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Modules\HotelContentRepository\Models\ImageGallery;

class ImageGalleryDTO
{
    public $id;
    public $gallery_name;
    public $description;
    public $images;

    public function __construct(ImageGallery $gallery)
    {
        $this->id = $gallery->id;
        $this->gallery_name = $gallery->gallery_name;
        $this->description = $gallery->description;
        $this->images = $gallery->images->map(function ($image) {
            return new ImageDTO($image);
        });
    }
}
