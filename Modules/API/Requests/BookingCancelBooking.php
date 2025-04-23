<?php

namespace Modules\API\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class BookingCancelBooking extends ApiRequest
{
    /**
     * @OA\Delete(
     *   tags={"Booking API | Booking"},
     *   path="/api/booking/cancel-booking",
     *   summary="Cancel an existing booking reservation. Submit a request to cancel a reservation you no longer require. ",
     *   description="Cancel Booking",
     *
     *    @OA\Parameter(
     *      name="booking_id",
     *      in="query",
     *      required=true,
     *      description="Booking ID",
     *      example="3333cee5-b4a3-4e51-bfb0-02d09370b585"
     *    ),
     *    @OA\Parameter(
     *      name="booking_item",
     *      in="query",
     *      description="To retrieve the **booking_item**, you need to execute a **'/api/pricing/search'** request. <br>
     *      In the response object for each rate is a **booking_item** property. <br>
     *      If there is no booking_item, all items will be deleted",
     *      example="c7bb44c1-bfaa-4d05-b2f8-37541b454f8c"
     *    ),
     *
     *    @OA\Response(
     *      response=200,
     *      description="OK",
     *
     *      @OA\JsonContent(
     *        ref="#/components/schemas/BookingCancelBookingResponse",
     *        examples={
     *        "example1": @OA\Schema(ref="#/components/examples/BookingCancelBookingResponse", example="BookingCancelBookingResponse"),
     *        }
     *      )
     *    ),
     *
     *    @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BadRequestResponse",
     *       examples={
     *       "example1": @OA\Schema(ref="#/components/examples/BadRequestResponse", example="BadRequestResponse"),
     *       }
     *      )
     *    ),
     *
     *    @OA\Response(
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

    public function rules(): array
    {
        return [
            'booking_id' => 'required|size:36',
            'booking_item' => 'nullable|size:36',
        ];
    }

    public function validatedDate(): array
    {
        return parent::validated();
    }
}
