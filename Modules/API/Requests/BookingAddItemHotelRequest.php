<?php

namespace Modules\API\Requests;

use Modules\API\Validate\ApiRequest;

class BookingAddItemHotelRequest extends ApiRequest
{
    /**
     * @OA\Post(
     *   tags={"Booking API | Basket"},
     *   path="/api/booking/add-item",
     *   summary="Add an item to your shopping cart.",
     *   description="Add an item to your shopping cart. This endpoint is used for adding products or services to your cart.",
     *
     *   @OA\RequestBody(
     *     required=true,
     *     description="Request payload. Fields booking_item and email_verification are required. You must also specify at least one of api_client.id or api_client.email.",
     *
     *     @OA\MediaType(
     *       mediaType="application/json",
     *
     *       @OA\Schema(
     *         required={"booking_item", "email_verification", "api_client"},
     *
     *         @OA\Property(
     *           property="booking_item",
     *           type="string",
     *           description="Booking item UUID from pricing search response",
     *           example="c7bb44c1-bfaa-4d05-b2f8-37541b454f8c"
     *         ),
     *         @OA\Property(
     *           property="booking_id",
     *           type="string",
     *           description="Optional booking_id if adding item to an existing booking",
     *           example="c698abfe-9bfa-45ee-a201-dc7322e008ab"
     *         ),
     *         @OA\Property(
     *           property="email_verification",
     *           type="string",
     *           format="email",
     *           description="Email where verification link will be sent",
     *           example="verify@example.com"
     *         ),
     *         @OA\Property(
     *           property="api_client",
     *           type="object",
     *           description="API client identification (must specify at least id or email)",
     *           @OA\Property(
     *             property="id",
     *             type="integer",
     *             example=12345
     *           ),
     *           @OA\Property(
     *             property="email",
     *             type="string",
     *             format="email",
     *             example="client@example.com"
     *           )
     *         )
     *       ),
     *
     *       @OA\Examples(
     *         example="withId",
     *         summary="Booking item with client ID",
     *         value={
     *           "booking_item"="c7bb44c1-bfaa-4d05-b2f8-37541b454f8c",
     *           "email_verification"="verify@example.com",
     *           "api_client"={"id"=5}
     *         }
     *       ),
     *       @OA\Examples(
     *         example="withEmail",
     *         summary="Booking item with client email",
     *         value={
     *           "booking_item"="c7bb44c1-bfaa-4d05-b2f8-37541b454f8c",
     *           "email_verification"="verify@example.com",
     *           "api_client"={"email"="client@example.com"}
     *         }
     *       ),
     *       @OA\Examples(
     *         example="withBookingId",
     *         summary="Booking item with existing booking_id and client email",
     *         value={
     *           "booking_item"="c7bb44c1-bfaa-4d05-b2f8-37541b454f8c",
     *           "booking_id"="c698abfe-9bfa-45ee-a201-dc7322e008ab",
     *           "email_verification"="verify@example.com",
     *           "api_client"={"email"="client@example.com"}
     *         }
     *       )
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *
     *     @OA\JsonContent(ref="#/components/schemas/BookingAddItemResponse")
     *   ),
     *
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *
     *     @OA\JsonContent(ref="#/components/schemas/BadRequestResponse")
     *   ),
     *
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *
     *     @OA\JsonContent(ref="#/components/schemas/UnAuthenticatedResponse")
     *   ),
     *   security={{ "apiAuth": {} }}
     * )
     */
    public function rules(): array
    {
        return [
            'booking_item' => 'required|size:36',
            'booking_id' => 'sometimes|size:36',

            'email_verification' => 'sometimes|email:rfc,dns',
            'api_client.id' => 'required_without:api_client.email|nullable|integer',
            'api_client.email' => 'required_without:api_client.id|nullable|email:rfc,dns',
        ];
    }

    public function validatedDate(): array
    {
        return parent::validated();
    }
}
