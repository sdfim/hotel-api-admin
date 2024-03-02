<?php

namespace Modules\API\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;


class BookingAddPassengersHotelRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
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
            'booking_id' => 'required|size:36',

            'passengers' => 'required|array',
            'passengers.*.title' => 'required|in:mr,Mr,MR,ms,Ms,MS,Mrs,MRS,mrs,Miss,MISS,miss,Dr,dr,DR,Prof,prof,PROF',
            'passengers.*.given_name' => 'required|string',
            'passengers.*.family_name' => 'required|string',
            'passengers.*.date_of_birth' => 'required|date_format:Y-m-d',
            'passengers.*.booking_items' => 'required|array',
            'passengers.*.booking_items.*.booking_item' => 'required|uuid',
            'passengers.*.booking_items.*.room' => 'numeric',
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
