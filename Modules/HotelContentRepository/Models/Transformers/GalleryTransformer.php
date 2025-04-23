<?php

namespace Modules\HotelContentRepository\Models\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\HotelContentRepository\Models\ImageGallery;

class GalleryTransformer extends TransformerAbstract
{
    public function transform(ImageGallery $gallery)
    {
        return [
            'gallery_name' => $gallery->gallery_name,
            'description' => $gallery->description,
            'images' => $gallery->images,
        ];
    }
}
