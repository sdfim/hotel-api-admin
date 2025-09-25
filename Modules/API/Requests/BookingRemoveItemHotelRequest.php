<?php

namespace Modules\API\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class BookingRemoveItemHotelRequest extends ApiRequest
{
    /**
     * @OA\Delete(
     *   tags={"Booking API | Basket"},
     *   path="/api/booking/remove-item",
     *   summary="Remove a specific item from your shopping cart",
     *   description="Description: Remove a specific item from your shopping cart. It allows you to modify the contents of your cart.",
     *
     *    @OA\Parameter(
     *      name="booking_id",
     *      in="query",
     *      required=true,
     *      description="**booking_id**",
     *      example="c698abfe-9bfa-45ee-a201-dc7322e008ab"
     *    ),
     *    @OA\Parameter(
     *      name="booking_item",
     *      in="query",
     *      required=true,
     *      description="To retrieve the **booking_item**, you need to execute a **'/api/pricing/search'** request. <br>
     *      In the response object for each rate is a **booking_item** property.",
     *      example="c7bb44c1-bfaa-4d05-b2f8-37541b454f8c"
     *    ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BookingRemoveItemResponse",
     *           examples={
     *             "example1": @OA\Schema(ref="#/components/examples/BookingRemoveItemResponse", example="BookingRemoveItemResponse"),
     *         },
     *      )
     *   ),
     *
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BadRequestResponse",
     *       examples={
     *       "example1": @OA\Schema(ref="#/components/examples/BadRequestResponse", example="BadRequestResponse"),
     *       }
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

    public function rules(): array
    {
        return [
            'booking_item' => 'required|size:36',
            'booking_id' => 'required|size:36',
        ];
    }

    public function validatedDate(): array
    {
        return parent::validated();
    }
}
