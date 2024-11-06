<?php

namespace Modules\HotelContentRepository\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HotelDepositInformationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'hotel_id' => 'required|exists:pd_hotels,id',
            'days_departure' => 'required|integer',
            'pricing_parameters' => 'required|string',
            'pricing_value' => 'required|numeric',
        ];
    }
}
