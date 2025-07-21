<?php

namespace Modules\HotelContentRepository\Actions\Gallery;

use Modules\HotelContentRepository\Models\ImageGallery;

class EditGallery
{
    public function execute(array $data, ImageGallery $gallery, array $imageIds): void
    {
        $exists = $gallery->exists;
        $gallery->fill($data);
        $gallery->save();

        if (! $exists) {
            $gallery->images()->attach($imageIds);
        }
    }
}
