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
     *   security={{ "apiAuth": {} }},
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
     *   @OA\Response(
     *     response=200,
     *     description="Successful response with available endpoints for modifying the booking.",
     *     @OA\JsonContent(
     *       example={
     *         "success": true,
     *         "data": {
     *           "check_quote_search_id": "35b2471c-5118-469f-84ea-52c11ee896ae",
     *           "comparison_of_amounts": {
     *             "current_search_sums": {
     *               "total_net": 23436,
     *               "total_tax": 0,
     *               "total_fees": 0,
     *               "total_price": 23436,
     *               "markup": 0
     *             },
     *             "first_search_sums": {
     *               "total_net": 11718,
     *               "total_tax": 0,
     *               "total_fees": 0,
     *               "total_price": 11718,
     *               "markup": 0
     *             },
     *             "differences": {
     *               "total_net": true,
     *               "total_tax": false,
     *               "total_fees": false,
     *               "total_price": true,
     *               "markup": false
     *             },
     *             "conclusion": "difference"
     *           },
     *           "hotel_image": "http://localhost:8007/storage/products/Velas-Vallarta.jpg",
     *           "attributes": {
     *             {"name": "Elevator", "category": "Elevator"},
     *             {"name": "Fitness facilities", "category": "Gym"},
     *             {"name": "Wheelchair accessible path of travel", "category": "Accessible Wheelchair"},
     *             {"name": "Conference space", "category": "general"},
     *             {"name": "Free WiFi", "category": "WiFi Included"},
     *             {"name": "Laundry facilities", "category": "general"},
     *             {"name": "Safe-deposit box at front desk", "category": "general"},
     *             {"name": "Multilingual staff", "category": "general"},
     *             {"name": "24-hour front desk", "category": "general"},
     *             {"name": "Business center", "category": "Business"},
     *             {"name": "Dry cleaning/laundry service", "category": "general"},
     *             {"name": "Internet access - wireless", "category": "general"},
     *             {"name": "Wedding services", "category": "general"},
     *             {"name": "Snack bar/deli", "category": "Restaurant"},
     *             {"name": "Computer station", "category": "general"},
     *             {"name": "ATM/banking", "category": "general"},
     *             {"name": "Hiking/biking trails nearby", "category": "general"},
     *             {"name": "Terrace", "category": "general"}
     *           },
     *           "email_verification": "kslndr@gmail.com",
     *           "check_quote_search_query": {
     *             "type": "hotel",
     *             "rating": 4,
     *             "checkin": "2026-01-03",
     *             "checkout": "2026-01-07",
     *             "supplier": {"HBSI"},
     *             "token_id": "Rx1rkXk0S9MomMpssi5D9T4kfUI5MwHxRZDSwYoHecbb4ace",
     *             "giata_ids": {"21569211"},
     *             "occupancy": {
     *               {"adults": 2},
     *               {"adults": 2}
     *             },
     *             "booking_item": "d9708814-5370-40ea-ba62-a79eaa837e88",
     *             "blueprint_exist": false
     *           },
     *           "giata_id": "21569211",
     *           "booking_item": "d3363136-e015-44f6-ba7e-7e2cc6d888e5",
     *           "current_search": {},
     *           "first_search": {}
     *         },
     *         "message": "success"
     *       }
     *     )
     *   ),
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
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse",
     *       examples={
     *         "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse")
     *       }
     *     )
     *   )
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
