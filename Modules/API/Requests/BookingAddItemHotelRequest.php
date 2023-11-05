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
			'booking_item' => 'required|size:36',
            'search_id' => 'string|size:36',
            'supplier' => ['string'],
            'hotel_id' => ['integer'],
            'room_id' => '|integer',
            'rate' => ['integer'],
            'bed_groups' => ['integer'],
            'hold' => ['string'],
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
