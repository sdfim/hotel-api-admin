<?php

namespace Modules\API\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class BookingPriceCheckBookHotelRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @OA\Get(
     *   tags={"Booking API | Change Booking"},
     *   path="/api/booking/change/price-check",
     *   summary="Retrieve Booking Price Check",
     *   description="This endpoint provides information about the price check of a booking item.",
     *
     *   @OA\Parameter(
     *     name="new_booking_item",
     *     in="query",
     *     description="The new booking item ID.",
     *     required=true,
     *     @OA\Schema(
     *       type="string",
     *       example="abcd1234-efgh5678-ijkl9012-mnop3456"
     *     )
     *   ),
     *
     *   @OA\Parameter(
     *     name="booking_id",
     *     in="query",
     *     description="The booking ID.",
     *     required=true,
     *     @OA\Schema(
     *       type="string",
     *       example="abcd1234-efgh5678-ijkl9012-mnop3456"
     *     )
     *   ),
     *
     *   @OA\Parameter(
     *     name="booking_item",
     *     in="query",
     *     description="The booking item ID.",
     *     required=true,
     *     @OA\Schema(
     *       type="string",
     *       example="abcd1234-efgh5678-ijkl9012-mnop3456"
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(
     *         property="success",
     *         type="boolean",
     *         example=true
     *       ),
     *       @OA\Property(
     *         property="data",
     *         type="object",
     *         @OA\Property(
     *           property="result",
     *           type="object",
     *           @OA\Property(
     *             property="incremental_total_price",
     *             type="integer",
     *             example=200
     *           ),
     *           @OA\Property(
     *             property="current_booking_item",
     *             type="object",
     *             @OA\Property(
     *               property="total_net",
     *               type="integer",
     *               example=400
     *             ),
     *             @OA\Property(
     *               property="total_tax",
     *               type="integer",
     *               example=0
     *             ),
     *             @OA\Property(
     *               property="total_fees",
     *               type="integer",
     *               example=0
     *             ),
     *             @OA\Property(
     *               property="total_price",
     *               type="integer",
     *               example=400
     *             ),
     *             @OA\Property(
     *               property="cancellation_policies",
     *               type="array",
     *               @OA\Items(
     *                 type="object",
     *                 @OA\Property(
     *                   property="type",
     *                   type="string",
     *                   example="General"
     *                 ),
     *                 @OA\Property(
     *                   property="percentage",
     *                   type="string",
     *                   example="20"
     *                 ),
     *                 @OA\Property(
     *                   property="description",
     *                   type="string",
     *                   example="General Cancellation Policy"
     *                 ),
     *                 @OA\Property(
     *                   property="penalty_start_date",
     *                   type="string",
     *                   format="date",
     *                   example="2024-12-24"
     *                 )
     *               )
     *             ),
     *             @OA\Property(
     *               property="breakdown",
     *               type="object",
     *               @OA\Property(
     *                 property="fees",
     *                 type="array",
     *                 @OA\Items(type="object")
     *               ),
     *               @OA\Property(
     *                 property="stay",
     *                 type="array",
     *                 @OA\Items(type="object")
     *               ),
     *               @OA\Property(
     *                 property="nightly",
     *                 type="array",
     *                 @OA\Items(
     *                   type="array",
     *                   @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                       property="type",
     *                       type="string",
     *                       example="base_rate"
     *                     ),
     *                     @OA\Property(
     *                       property="title",
     *                       type="string",
     *                       example="Base Rate"
     *                     ),
     *                     @OA\Property(
     *                       property="amount",
     *                       type="number",
     *                       format="float",
     *                       example=181.82
     *                     )
     *                   )
     *                 )
     *               )
     *             ),
     *             @OA\Property(
     *               property="rate_name",
     *               type="string",
     *               example="Best"
     *             ),
     *             @OA\Property(
     *               property="room_name",
     *               type="string",
     *               example="Single"
     *             ),
     *             @OA\Property(
     *               property="currency",
     *               type="string",
     *               example="USD"
     *             ),
     *             @OA\Property(
     *               property="booking_item",
     *               type="string",
     *               example="290d6b20-aeb1-4e3b-85e5-b2d2816ecd30"
     *             ),
     *             @OA\Property(
     *               property="hotelier_booking_reference",
     *               type="string",
     *               example="721FlQnbUW"
     *             )
     *           ),
     *           @OA\Property(
     *             property="new_booking_item",
     *             type="object",
     *             @OA\Property(
     *               property="total_net",
     *               type="integer",
     *               example=600
     *             ),
     *             @OA\Property(
     *               property="total_tax",
     *               type="integer",
     *               example=0
     *             ),
     *             @OA\Property(
     *               property="total_fees",
     *               type="integer",
     *               example=0
     *             ),
     *             @OA\Property(
     *               property="total_price",
     *               type="integer",
     *               example=600
     *             ),
     *             @OA\Property(
     *               property="cancellation_policies",
     *               type="array",
     *               @OA\Items(
     *                 type="object",
     *                 @OA\Property(
     *                   property="type",
     *                   type="string",
     *                   example="General"
     *                 ),
     *                 @OA\Property(
     *                   property="percentage",
     *                   type="string",
     *                   example="100"
     *                 ),
     *                 @OA\Property(
     *                   property="description",
     *                   type="string",
     *                   example="General Cancellation Policy"
     *                 ),
     *                 @OA\Property(
     *                   property="penalty_start_date",
     *                   type="string",
     *                   format="date",
     *                   example="2024-08-07"
     *                 )
     *               )
     *             ),
     *             @OA\Property(
     *               property="breakdown",
     *               type="object",
     *               @OA\Property(
     *                 property="fees",
     *                 type="array",
     *                 @OA\Items(type="object")
     *               ),
     *               @OA\Property(
     *                 property="stay",
     *                 type="array",
     *                 @OA\Items(type="object")
     *               ),
     *               @OA\Property(
     *                 property="nightly",
     *                 type="array",
     *                 @OA\Items(
     *                   type="array",
     *                   @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                       property="type",
     *                       type="string",
     *                       example="base_rate"
     *                     ),
     *                     @OA\Property(
     *                       property="title",
     *                       type="string",
     *                       example="Base Rate"
     *                     ),
     *                     @OA\Property(
     *                       property="amount",
     *                       type="number",
     *                       format="float",
     *                       example=272.73
     *                     )
     *                   )
     *                 )
     *               )
     *             ),
     *             @OA\Property(
     *               property="rate_name",
     *               type="string",
     *               example="Promo"
     *             ),
     *             @OA\Property(
     *               property="room_name",
     *               type="string",
     *               example="Suite"
     *             ),
     *             @OA\Property(
     *               property="currency",
     *               type="string",
     *               example="USD"
     *             ),
     *             @OA\Property(
     *               property="booking_item",
     *               type="string",
     *               example="290d6b20-aeb1-4e3b-85e5-b2d2816ecd30"
     *             )
     *           )
     *         ),
     *       ),
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="success"
     *       )
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
     *         "example1": @OA\Schema(ref="#/components/examples/BadRequestResponse", example="BadRequestResponse"),
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
     *         "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse"),
     *       }
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
            'new_booking_item' => 'required|string|size:36',
            'booking_id' => 'required|string|size:36',
            'booking_item' => 'required|string|size:36',
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
