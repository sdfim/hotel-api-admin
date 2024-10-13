<?php

namespace Modules\HotelContentRepository\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class HotelRoomRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'hotel_id' => 'required|integer',
            'room_name' => 'required|string|max:255',
            'hbs_data_mapped_name' => 'required|string|max:255',
            'room_description' => 'required|string',
        ];
    }
}
