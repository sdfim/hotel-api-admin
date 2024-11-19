<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class InformativeServiceRetrieveRequest extends ApiRequest
{    public function rules(): array
    {
        return [
            'booking_item' => 'required|string|exists:api_booking_items,booking_item',
        ];
    }
}
