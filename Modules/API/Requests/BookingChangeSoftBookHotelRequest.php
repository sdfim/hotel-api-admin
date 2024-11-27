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
     *   @OA\RequestBody(
     *     description="JSON object containing the details of the reservation.",
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(
     *         property="booking_id",
     *         type="string",
     *         description="ID of the booking",
     *         example="a7b8c9d0-e1f2-3456-7890-abcd1234efgh"
     *       ),
     *       @OA\Property(
     *         property="booking_item",
     *         type="string",
     *         description="ID of the booking item",
     *         example="f1e2d3c4-b5a6-7890-cd12-ef34gh56ij78"
     *       ),
     *       @OA\Property(
     *         property="passengers",
     *         type="array",
     *         @OA\Items(
     *           type="object",
     *           @OA\Property(
     *             property="title",
     *             type="string",
     *             description="Title of the passenger",
     *             example="Mr"
     *           ),
     *           @OA\Property(
     *             property="given_name",
     *             type="string",
     *             description="Given name of the passenger",
     *             example="John"
     *           ),
     *           @OA\Property(
     *             property="family_name",
     *             type="string",
     *             description="Family name of the passenger",
     *             example="Doe"
     *           ),
     *           @OA\Property(
     *             property="room",
     *             type="integer",
     *             description="Room number",
     *             example=1
     *           )
     *         )
     *       ),
     *       @OA\Property(
     *         property="special_requests",
     *         type="array",
     *         @OA\Items(
     *           type="object",
     *           @OA\Property(
     *             property="special_request",
     *             type="string",
     *             description="Special request",
     *             example="Late check-in"
     *           ),
     *           @OA\Property(
     *             property="room",
     *             type="integer",
     *             description="Room number for the special request",
     *             example=101
     *           )
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Booking successfully changed.",
     *     @OA\JsonContent(
     *       @OA\Property(
     *         property="success",
     *         type="boolean",
     *         example=true
     *       ),
     *       @OA\Property(
     *         property="data",
     *         type="object",
     *         @OA\Property(
     *           property="status",
     *           type="string",
     *           example="Booking changed."
     *         )
     *       ),
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="success"
     *       )
     *     )
     *   ),
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

    public function rules(): array
    {
        return [
            'booking_id' => 'required|size:36',
            'booking_item' => 'required|size:36',

            'passengers' => 'required_without:query|array',
            'passengers.*.title' => 'required_with:passengers|in:mr,Mr,MR,ms,Ms,MS,Mrs,MRS,mrs,Miss,MISS,miss,Dr,dr,DR,Prof,prof,PROF',
            'passengers.*.given_name' => 'required_with:passengers|string|between:1,255',
            'passengers.*.family_name' => 'required_with:passengers|string|between:1,255',
            'passengers.*.room' => 'numeric',

            'special_requests' => 'array',
            'special_requests.*.special_request' => 'string|max:255',
            'special_requests.*.room' => 'numeric',
        ];
    }

    public function validatedDate(): array
    {
        return parent::validated();
    }
}
