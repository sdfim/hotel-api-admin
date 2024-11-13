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
            'services' => 'required|array',
            'services.*.service_id' => 'required_without:services.*.service_name|integer|exists:config_service_types,id',
            'services.*.service_name' => 'required_without:services.*.service_id|string|exists:config_service_types,name',
            'services.*.cost' => 'required|numeric',
        ];
    }
}
