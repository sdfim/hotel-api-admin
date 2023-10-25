<?php

namespace Modules\API\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Modules\API\Validate\ApiRequest;
use Illuminate\Support\Facades\Auth;

class BookingAddItemHotelRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search_id' => 'required|size:36',
            'supplier' => ['required', 'string'],
            'hotel_id' => ['required', 'integer'],
            'room_id' => 'required|integer',
            'rate' => ['required', 'integer'],
            'bed_groups' => ['required', 'integer'],
            'hold' => ['required', 'string'],
        ];
    }

    /**
     * @return array
     */
    public function validatedDate(): array
    {
        return parent::validated();
    }
}
