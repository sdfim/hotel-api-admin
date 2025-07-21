<?php

namespace Modules\HotelContentRepository\API\Requests;

use Modules\API\Validate\ApiRequest;

class ContentSourceRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }
}
