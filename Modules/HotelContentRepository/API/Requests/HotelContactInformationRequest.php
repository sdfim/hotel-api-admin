<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HotelContactInformationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'hotel_id' => 'required|integer|exists:pd_hotels,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'contactInformations' => 'array',
            'contactInformations.*' => 'integer|exists:config_job_descriptions,id',
        ];
    }
}
