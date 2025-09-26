<?php

namespace Modules\API\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class BookingCheckQuoteRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    /**
     * @OA\Post(
     *   tags={"Booking API | Quote"},
     *   path="/api/booking/check-quote",
     *   summary="Retrieve a specific booking quote by booking_item.",
     *   description="This endpoint provides information about actual availability and pricing for a specific booking quote (unbooked cart item) for the agent.",
     *
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"booking_item"},
     *       @OA\Property(
     *         property="booking_item",
     *         type="string",
     *         format="uuid",
     *         example="123e4567-e89b-12d3-a456-426614174000"
     *       )
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Successful response with available endpoints for modifying the booking.",
     *     @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example=true),
     *       @OA\Property(
     *         property="data",
     *         type="object",
     *         @OA\Property(property="booking_item", type="string", example="290d6b20-aeb1-4e3b-85e5-b2d2816ecd30"),
     *         @OA\Property(property="non_refundable", type="boolean", example=false),
     *         @OA\Property(property="supplier", type="string", example="HBSI"),
     *         @OA\Property(
     *           property="endpoints",
     *           type="array",
     *           @OA\Items(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Soft Change"),
     *             @OA\Property(property="description", type="string", example="Endpoint to handle soft changes in booking."),
     *             @OA\Property(property="url", type="string", example="api/booking/change/soft-change")
     *           )
     *         )
     *       ),
     *       @OA\Property(property="message", type="string", example="success")
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BadRequestResponse",
     *       examples={
     *         "example1": @OA\Schema(ref="#/components/examples/BadRequestResponse", example="BadRequestResponse")
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse",
     *       examples={
     *         "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse")
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
            'booking_item' => 'required|size:36',
        ];
    }

    public function validatedDate(): array
    {
        return parent::validated();
    }
}
