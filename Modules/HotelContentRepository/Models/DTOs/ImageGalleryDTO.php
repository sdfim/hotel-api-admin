<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Illuminate\Database\Eloquent\Collection;
use Modules\HotelContentRepository\Models\ImageGallery;

class ImageGalleryDTO
{
    public $id;

    public $gallery_name;

    public $description;

    public $images;

    public function __construct(
        private readonly ImageDTO $imageDTO
    ) {}

    public function transform(Collection $galleries)
    {
        return $galleries->map(function ($gallery) {
            return $this->transformGallery($gallery);
        })->all();
    }

    public function transformGallery(ImageGallery $gallery)
    {
        return [
            'id' => $gallery->id,
            'gallery_name' => $gallery->gallery_name,
            'description' => $gallery->description,
            'images' => $this->imageDTO->transform($gallery->images),
        ];
    }
}
