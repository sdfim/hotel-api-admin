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
            'per_channel' => 'nullable|numeric',
            'per_room' => 'nullable|numeric',
            'per_rate' => 'nullable|numeric',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->per_channel && !$this->per_room && !$this->per_rate) {
                $validator->errors()->add('per_channel', 'At least one of per_channel, per_room, or per_rate must be provided.');
            }
        });
    }
}
