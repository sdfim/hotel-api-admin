<?php

namespace Modules\HotelContentRepository\Actions\Gallery;

use Illuminate\Database\Eloquent\Collection;
use Modules\HotelContentRepository\Models\Image;
use Modules\HotelContentRepository\Models\ImageGallery;

class DeleteGallery
{
    public function detachImage(Image $image, ImageGallery $gallery, array &$imageIds): void
    {
        if ($gallery->exists) {
            $gallery->images()->detach($image->id);
        } else {
            $imageIds = array_filter($imageIds, fn ($id) => $id != $image->id);
        }
    }

    public function detachImages(Collection $records, ImageGallery $gallery, array &$imageIds): void
    {
        if ($gallery->exists) {
            $gallery->images()->detach($records->pluck('id'));
        } else {
            $ids = $records->pluck('id')->all();
            $imageIds = array_filter($imageIds, fn ($id) => ! in_array($id, $ids));
        }
    }
}
