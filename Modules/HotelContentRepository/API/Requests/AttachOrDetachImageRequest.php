<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class AttachOrDetachImageRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'image_id' => 'required|exists:pd_images,id',
        ];
    }
}
