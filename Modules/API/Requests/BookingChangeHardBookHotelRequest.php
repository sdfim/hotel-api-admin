<?php

namespace Modules\API\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class BookingChangeHardBookHotelRequest extends ApiRequest
{
    /**
     * @OA\Put(
     *   tags={"Booking API | Change Booking"},
     *   path="/api/booking/change/hard-change",
     *   summary="Hard Modify an existing booking.",
     *   description="Hard modify an existing booking. This endpoint allows for significant changes to the booking, such as replacing the booking item.",
     *
     *   @OA\RequestBody(
     *     description="JSON object containing the details of the modification.",
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(
     *         property="booking_id",
     *         description="UUID of the existing booking.",
     *         type="string",
     *         format="uuid"
     *       ),
     *       @OA\Property(
     *         property="booking_item",
     *         description="UUID of the existing booking item to be modified.",
     *         type="string",
     *         format="uuid"
     *       ),
     *       @OA\Property(
     *         property="new_booking_item",
     *         description="UUID of the new booking item to replace the existing one.",
     *         type="string",
     *         format="uuid"
     *       ),
     *       @OA\Property(
     *         property="passengers",
     *         type="array",
     *         @OA\Items(
     *           type="object",
     *           @OA\Property(
     *             property="title",
     *             description="Title of the passenger.",
     *             type="string",
     *             enum={"mr", "Mr", "MR", "ms", "Ms", "MS", "Mrs", "MRS", "mrs", "Miss", "MISS", "miss", "Dr", "dr", "DR", "Prof", "prof", "PROF"}
     *           ),
     *           @OA\Property(
     *             property="given_name",
     *             description="Given name of the passenger.",
     *             type="string",
     *             maxLength=255
     *           ),
     *           @OA\Property(
     *             property="family_name",
     *             description="Family name of the passenger.",
     *             type="string",
     *             maxLength=255
     *           ),
     *           @OA\Property(
     *             property="date_of_birth",
     *             description="Date of birth of the passenger.",
     *             type="string",
     *             format="date"
     *           ),
     *           @OA\Property(
     *             property="room",
     *             description="Room number or identifier for the passenger.",
     *             type="integer"
     *           )
     *         )
     *       )
     *     )
     *   ),
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
            'booking_id' => 'required|uuid',
            'booking_item' => 'required|uuid',
            'new_booking_item' => 'required|uuid',

            'passengers' => 'array',
            'passengers.*.title' => 'required_with:passengers|in:mr,Mr,MR,ms,Ms,MS,Mrs,MRS,mrs,Miss,MISS,miss,Dr,dr,DR,Prof,prof,PROF',
            'passengers.*.given_name' => 'required_with:passengers|string|between:1,255',
            'passengers.*.family_name' => 'required_with:passengers|string|between:1,255',
            'passengers.*.date_of_birth' => 'required_with:passengers|date_format:Y-m-d',
            'passengers.*.room' => 'numeric',
        ];
    }
}
