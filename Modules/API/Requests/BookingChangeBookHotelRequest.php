<?php

namespace Modules\API\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class BookingChangeBookHotelRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    /**
     * @OA\Put(
     *   tags={"Booking API | Booking Endpoints"},
     *   path="/api/booking/change-booking",
     *   summary="Modify an existing booking..",
     *   description="Modify an existing booking. You can update booking details, change dates, or make other adjustments to your reservation.",
     *
     *   @OA\Parameter(
     *      name="booking_id",
     *      in="query",
     *      required=true,
     *      description="Booking ID",
     *      example="3333cee5-b4a3-4e51-bfb0-02d09370b585"
     *    ),
     *   @OA\Parameter(
     *      name="booking_item",
     *      in="query",
     *      required=true,
     *      description="To retrieve the **booking_item**, you need to execute a **'/api/pricing/search'** request. <br>
     *      In the response object for each rate is a **booking_item** property.",
     *      example="c7bb44c1-bfaa-4d05-b2f8-37541b454f8c"
     *    ),
     *
     *     @OA\RequestBody(
     *     description="JSON object containing the details of the reservation.",
     *     required=true,
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BookingChangeBookingRequest",
     *       examples={
     *           "example1": @OA\Schema(ref="#/components/examples/BookingChangeBookingRequest", example="BookingChangeBookingRequest"),
     *       },
     *     ),
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BookingChangeBookingResponse",
     *           examples={
     *             "example1": @OA\Schema(ref="#/components/examples/BookingChangeBookingResponse", example="BookingChangeBookingResponse"),
     *         },
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *
     *     @OA\JsonContent(
     *       examples={
     *         "example1": @OA\Schema(ref="#/components/examples/BookingChangeBookingResponseError", example="BookingChangeBookingResponseError"),
     *       },
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse",
     *       examples={
     *       "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse"),
     *       }
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * )
     */


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
