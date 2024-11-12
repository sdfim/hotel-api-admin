<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class InformativeServiceRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'booking_item' => 'required|string|exists:api_booking_items,booking_item',
            'service_id' => 'required_without:service_name|integer|exists:config_service_types,id',
            'service_name' => 'required_without:service_id|string|exists:config_service_types,name',
            'cost' => 'decimal',
        ];
    }
}
