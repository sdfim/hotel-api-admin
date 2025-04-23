<?php

namespace Modules\API\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class BookingChangeBookHotelRequest extends ApiRequest
{
    /**
     * @OA\Put(
     *   tags={"Booking API | Change Booking"},
     *   path="/api/booking/change-booking",
     *   summary="Modify an existing booking.",
     *   description="Modify an existing booking. You can update booking details, change dates, or make other adjustments to your reservation.",
     *
     *   @OA\RequestBody(
     *     description="JSON object containing the details of the modification.",
     *     required=true,
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BookingChangeBookingRequest"
     *     )
     *   ),
     *   @OA\Response(
     *      response=200,
     *      description="Booking successfully changed.",
     *      @OA\JsonContent(
     *        @OA\Property(
     *          property="success",
     *          type="boolean",
     *          example=true
     *        ),
     *        @OA\Property(
     *          property="data",
     *          type="object",
     *          @OA\Property(
     *            property="status",
     *            type="string",
     *            example="Booking changed."
     *          )
     *        ),
     *        @OA\Property(
     *          property="message",
     *          type="string",
     *          example="success"
     *        )
     *      )
     *    ),
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
            'query' => 'required|array',
            'query.given_name' => 'required|string|between:1,255',
            'query.family_name' => 'required|string|between:1,255',
            'query.smoking' => 'required|boolean',
            'query.special_request' => 'string|max:255',
            'query.loyalty_id' => 'string|max:10',
        ];
    }

    public function validatedDate(): array
    {
        return parent::validated();
    }
}
