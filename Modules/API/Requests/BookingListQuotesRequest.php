<?php

namespace Modules\API\Requests;

use Modules\API\Validate\ApiRequest;

/**
 * @OA\Get(
 *   tags={"Booking API | Quote"},
 *   path="/api/booking/list-quote",
 *   summary="List booking quotes (unbooked cart items) for the agent.",
 *   description="Returns a list of booking_ids (booking_items) that are not yet booked for the agent but are in the cart.",
 *
 *   @OA\Parameter(
 *      name="api_client_id",
 *      in="query",
 *      required=true,
 *      description="API client user ID. Either api_client_id or api_client_email must be provided.",
 *      @OA\Schema(type="integer", example=123)
 *   ),
 *   @OA\Parameter(
 *      name="api_client_email",
 *      in="query",
 *      required=true,
 *      description="API client email. Either api_client_id or api_client_email must be provided.",
 *
 *      @OA\Schema(type="string", format="email", example="user@example.com")
 *   ),
 *
 *   @OA\Parameter(
 *      name="booking_date_from",
 *      in="query",
 *      required=false,
 *      description="Filter quotes created from this date (YYYY-MM-DD).",
 *
 *      @OA\Schema(type="string", format="date", example="2025-09-01")
 *   ),
 *
 *   @OA\Parameter(
 *      name="booking_date_to",
 *      in="query",
 *      required=false,
 *      description="Filter quotes created up to this date (YYYY-MM-DD).",
 *
 *      @OA\Schema(type="string", format="date", example="2025-09-30")
 *   ),
 *
 *   @OA\Parameter(
 *      name="page",
 *      in="query",
 *      required=false,
 *      description="Page number for pagination.",
 *
 *      @OA\Schema(type="integer", example=1)
 *   ),
 *
 *   @OA\Parameter(
 *      name="results_per_page",
 *      in="query",
 *      required=false,
 *      description="Number of results per page.",
 *
 *      @OA\Schema(type="integer", example=20)
 *   ),
 *
 *   @OA\Response(
 *     response=200,
 *     description="Successful operation",
 *
 *     @OA\JsonContent(
 *       example={
 * "success": true,
 * "data": {
 * "count": 6,
 * "page": 2,
 * "results_per_page": 2,
 * "quotes": {
 * {
 * "booking_item": "77cbee37-a3b4-47e3-b6ed-3c60b597b008",
 * "email_verified": false,
 * "rooms": {
 * {
 * "room": "room 1",
 * "unified_room_code": "External-ZENPOOL",
 * "room_type": "ZENPOOL",
 * "rate_plan_code": "RO2",
 * "rate_name": "Room Only",
 * "supplier_room_name": "Nature View Suite with Pool - Zen Experience",
 * "booking_item": "77cbee37-a3b4-47e3-b6ed-3c60b597b008",
 * "non_refundable": false,
 * "currency": "USD",
 * "total_net": 6081,
 * "total_tax": 0,
 * "total_price": 6081,
 * "total_fees": 0,
 * "commissionable_amount": 6081,
 * "markup": 0,
 * "breakdown": {
 * "fees": {},
 * "stay": {},
 * "nightly": {
 * {
 * {
 * "type": "base_rate",
 * "title": "Base Rate",
 * "amount": "2192.00",
 * "rack_amount": "2192.00"
 * }
 * },
 * {
 * {
 * "type": "base_rate",
 * "title": "Base Rate",
 * "amount": "2051.00",
 * "rack_amount": "2051.00"
 * }
 * },
 * {
 * {
 * "type": "base_rate",
 * "title": "Base Rate",
 * "amount": "1838.00",
 * "rack_amount": "1838.00"
 * }
 * }
 * }
 * },
 * "cancellation_policies": {
 * {
 * "type": "General",
 * "level": "rate",
 * "currency": "USD",
 * "percentage": "100",
 * "description": "General Cancellation Policy",
 * "penalty_start_date": "2025-11-02"
 * },
 * {
 * "type": "NoShow",
 * "level": "rate",
 * "amount": "6081.00",
 * "currency": "USD",
 * "percentage": "100",
 * "description": "Penalty for no show."
 * }
 * },
 * "capacity": {
 * "adults": 2,
 * "unknown": 0,
 * "children": {}
 * },
 * "rate_description": "24 hours Buttler Concierge service \nPool & Beach Concierge \nBreakfast, lunch and dinner a la carte or buffet at Bistro, Azul and ChakÃ¡ Restaurants \nDinner a la carte at Frida Mexican Gourmet Cuisine ",
 * "room_description": "Deluxe Suite with 1 king bed or 2 queen beds, living area, jacuzzi and terrace",
 * "penalty_date": "2025-11-02",
 * "meal_plan": "",
 * "amenities": {},
 * "promotions": {},
 * "rate_id": "1",
 * "distribution": false,
 * "package_deal": false,
 * "query_package": "",
 * "giata_room_code": "263",
 * "giata_room_name": "",
 * "supplier_room_id": "2-0-0",
 * "bed_configurations": {},
 * "descriptive_content": {},
 * "pricing_rules_applier": {
 * "list": {},
 * "count": 0
 * },
 * "per_day_rate_breakdown": "",
 * "deposits": {},
 * "commission_amount": 0
 * }
 * }
 * },
 * {
 * "booking_item": "adea0667-3ccb-4706-a2dc-2fe4c7b1f36e",
 * "email_verified": false,
 * "rooms": {
 * {
 * "room": "room 1",
 * "unified_room_code": "External-AMB",
 * "room_type": "AMB",
 * "rate_plan_code": "RO2",
 * "rate_name": "Room Only",
 * "supplier_room_name": "Ambassador Suite Ocean View",
 * "booking_item": "adea0667-3ccb-4706-a2dc-2fe4c7b1f36e",
 * "non_refundable": false,
 * "currency": "USD",
 * "total_net": 8463,
 * "total_tax": 0,
 * "total_price": 8463,
 * "total_fees": 0,
 * "commissionable_amount": 8463,
 * "markup": 0,
 * "breakdown": {
 * "fees": {},
 * "stay": {},
 * "nightly": {
 * {
 * {
 * "type": "base_rate",
 * "title": "Base Rate",
 * "amount": "3061.00",
 * "rack_amount": "3061.00"
 * }
 * },
 * {
 * {
 * "type": "base_rate",
 * "title": "Base Rate",
 * "amount": "3061.00",
 * "rack_amount": "3061.00"
 * }
 * },
 * {
 * {
 * "type": "base_rate",
 * "title": "Base Rate",
 * "amount": "2341.00",
 * "rack_amount": "2341.00"
 * }
 * }
 * }
 * },
 * "cancellation_policies": {
 * {
 * "type": "General",
 * "level": "rate",
 * "currency": "USD",
 * "percentage": "100",
 * "description": "General Cancellation Policy",
 * "penalty_start_date": "2025-11-02"
 * },
 * {
 * "type": "NoShow",
 * "level": "rate",
 * "amount": "8463.00",
 * "currency": "USD",
 * "percentage": "100",
 * "description": "Penalty for no show."
 * }
 * },
 * "capacity": {
 * "adults": 2,
 * "unknown": 0,
 * "children": {}
 * },
 * "rate_description": "24 hours Buttler Concierge service \nPool & Beach Concierge \nBreakfast, lunch and dinner a la carte or buffet at Bistro, Azul and ChakÃ¡ Restaurants \nDinner a la carte at Frida Mexican Gourmet Cuisine ",
 * "room_description": "Deluxe Suite with 1 king bed or 2 queen beds, living area and terrace",
 * "penalty_date": "2025-11-02",
 * "meal_plan": "",
 * "amenities": {},
 * "promotions": {},
 * "rate_id": "7",
 * "distribution": false,
 * "package_deal": false,
 * "query_package": "",
 * "giata_room_code": "264",
 * "giata_room_name": "",
 * "supplier_room_id": "2-0-0",
 * "bed_configurations": {},
 * "descriptive_content": {},
 * "pricing_rules_applier": {
 * "list": {},
 * "count": 0
 * },
 * "per_day_rate_breakdown": "",
 * "deposits": {},
 * "commission_amount": 0
 * }
 * }
 * }
 * }
 * },
 * "message": "success"
 * }
 *     )
 *   ),
 *
 *   @OA\Response(
 *     response=400,
 *     description="Bad request (validation errors)",
 *
 *     @OA\JsonContent(ref="#/components/schemas/BadRequestResponse")
 *   ),
 *
 *   @OA\Response(
 *     response=401,
 *     description="Unauthenticated",
 *
 *     @OA\JsonContent(ref="#/components/schemas/UnAuthenticatedResponse")
 *   )
 * )
 */
class BookingListQuotesRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'api_client_id' => 'required_without:api_client_email|string',
            'api_client_email' => 'required_without:api_client_id|string|email',
            'booking_date_from' => 'nullable|date',
            'booking_date_to' => 'nullable|date',
            'page' => 'nullable|integer|min:1',
            'results_per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
