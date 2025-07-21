<?php

namespace Modules\HotelContentRepository\API\Requests;

use Modules\API\Validate\ApiRequest;

class AttachOrDetachWebFinderRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'web_finder_id' => 'required|exists:pd_hotel_web_finders,id',
        ];
    }
}
