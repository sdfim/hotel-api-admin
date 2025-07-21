<?php

namespace Modules\HotelContentRepository\API\Requests;

use Modules\API\Validate\ApiRequest;

class AttachOrDetachGalleryRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'gallery_id' => 'required|exists:pd_image_galleries,id',
        ];
    }
}
