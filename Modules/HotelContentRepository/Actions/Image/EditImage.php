<?php

namespace Modules\HotelContentRepository\Actions\Image;

use Modules\HotelContentRepository\Models\Image;

class EditImage
{
    public function execute(array $data, Image $image, array $galleries): void
    {
        $image->fill($data);
        $image->save();
        $image->galleries()->sync($galleries);
    }
}
