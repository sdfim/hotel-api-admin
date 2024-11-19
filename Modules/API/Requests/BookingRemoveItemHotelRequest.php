<?php

namespace Modules\API\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class BookingRemoveItemHotelRequest extends ApiRequest
{
    /**
     * @OA\Post(
     *   tags={"Booking API | Cart Endpoints"},
     *   path="/api/booking/add-item",
     *   summary="Add an item to your shopping cart.",
     *   description="Add an item to your shopping cart. This endpoint is used for adding products or services to your cart.",
     *
     *    @OA\Parameter(
     *      name="booking_item",
     *      in="query",
     *      required=true,
     *      description="To retrieve the **booking_item**, you need to execute a **'/api/pricing/search'** request. <br>
     *      In the response object for each rate is a **booking_item** property.",
     *      example="c7bb44c1-bfaa-4d05-b2f8-37541b454f8c"
     *    ),
     *    @OA\Parameter(
     *      name="booking_id",
     *      in="query",
     *      description="**booking_id**, if it exists",
     *      example="c698abfe-9bfa-45ee-a201-dc7322e008ab"
     *    ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BookingAddItemResponse",
     *           examples={
     *             "example1": @OA\Schema(ref="#/components/examples/BookingAddItemResponse", example="BookingAddItemResponse"),
     *         },
     *     )
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
