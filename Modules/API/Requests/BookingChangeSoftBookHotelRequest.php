<?php

namespace Modules\API\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class BookingChangeSoftBookHotelRequest extends ApiRequest
{
    /**
     * @OA\Put(
     *   tags={"Booking API | Change Booking"},
     *   path="/api/booking/change/soft-change",
     *   summary="Soft Modify an existing booking.",
     *   description="Modify an existing booking. You can update booking details, change dates, or make other adjustments to your reservation.",
     *
     *
     *   @OA\RequestBody(
     *     description="JSON object containing the details of the reservation.",
     *     required=true,
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BookingChangeSoftBookingRequest"
     *     )
     *   ),
     *
     *
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse"
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * )
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
            'booking_item' => 'required|size:36',
            'query' => 'required_without:passengers|array',
            'query.given_name' => 'required_with:query|string|between:1,255',
            'query.family_name' => 'required_with:query|string|between:1,255',
            'query.smoking' => 'required_with:query|boolean',
            'query.special_request' => 'string|max:255',
            'query.loyalty_id' => 'string|max:10',

            'passengers' => 'required_without:query|array',
            'passengers.*.title' => 'required_with:passengers|in:mr,Mr,MR,ms,Ms,MS,Mrs,MRS,mrs,Miss,MISS,miss,Dr,dr,DR,Prof,prof,PROF',
            'passengers.*.given_name' => 'required_with:passengers|string|between:1,255',
            'passengers.*.family_name' => 'required_with:passengers|string|between:1,255',
            'passengers.*.date_of_birth' => 'required_with:passengers|date_format:Y-m-d',
            'passengers.*.room' => 'numeric',
        ];
    }

    public function validatedDate(): array
    {
        return parent::validated();
    }
}
