<?php

namespace Modules\API\Requests;

use Modules\API\Validate\ApiRequest;

/**
 * @OA\Get(
 *   tags={"Booking API | Quote"},
 *   path="/api/booking/retrieve-quote",
 *   summary="Retrieve a specific booking quote by booking_item.",
 *   description="Returns details for a specific booking quote (unbooked cart item) for the agent.",
 *
 *   @OA\Parameter(
 *      name="api_client_email",
 *      in="query",
 *      required=true,
 *      description="API client email.",
 *      @OA\Schema(type="string", format="email", example="agent@example.com")
 *   ),
 *
 *   @OA\Parameter(
 *      name="booking_item",
 *      in="query",
 *      required=true,
 *      description="Booking item UUID.",
 *      @OA\Schema(type="string", format="uuid", example="57e7013d-12aa-4b9e-8c7a-b269aa6b8114")
 *   ),
 *
 *   @OA\Response(
 *     response=200,
 *     description="Successful operation",
 *     @OA\JsonContent(
 *       example={
 *         "success": true,
 *         "data": {
 *           "quotes": {
 *             {
 *               "booking_item": "57e7013d-12aa-4b9e-8c7a-b269aa6b8114",
 *               "email_verified": true,
 *               "rooms": {
 *                 {
 *                   "room": "room 1",
 *                   "room_type": "GCL",
 *                   "rate_plan_code": "RO2",
 *                   "supplier_room_name": "Grand Class Pool Suite Ocean Front",
 *                   "unified_room_code": "External-GCL",
 *                   "non_refundable": false,
 *                   "currency": "USD",
 *                   "total_net": 15194,
 *                   "total_tax": 0,
 *                   "total_price": 15194,
 *                   "total_fees": 0,
 *                   "markup": 0,
 *                   "breakdown": {
 *                     "fees": {},
 *                     "stay": {},
 *                     "nightly": {
 *                       {
 *                         {
 *                           "type": "base_rate",
 *                           "title": "Base Rate",
 *                           "amount": "2512.00",
 *                           "rack_amount": "2512.00"
 *                         }
 *                       },
 *                       {
 *                         {
 *                           "type": "base_rate",
 *                           "title": "Base Rate",
 *                           "amount": "1829.00",
 *                           "rack_amount": "1829.00"
 *                         }
 *                       }
 *                     }
 *                   },
 *                   "cancellation_policies": {
 *                     {
 *                       "type": "General",
 *                       "level": "rate",
 *                       "currency": "USD",
 *                       "percentage": "100",
 *                       "description": "General Cancellation Policy",
 *                       "penalty_start_date": "2025-11-04"
 *                     },
 *                     {
 *                       "type": "NoShow",
 *                       "level": "rate",
 *                       "amount": "8682.00",
 *                       "currency": "USD",
 *                       "percentage": "100",
 *                       "description": "Penalty for no show."
 *                     }
 *                   },
 *                   "rate_description": "24 hours Butler Concierge service...",
 *                   "meal_plan": ";",
 *                   "rate_id": "4;8",
 *                   "giata_room_code": "265",
 *                   "giata_room_name": ";",
 *                   "supplier_room_id": "2-0-0"
 *                 }
 *               }
 *             }
 *           }
 *         },
 *         "message": "success"
 *       }
 *     )
 *   ),
 *
 *   @OA\Response(
 *     response=400,
 *     description="Bad request (validation errors)",
 *     @OA\JsonContent(ref="#/components/schemas/BadRequestResponse")
 *   ),
 *
 *   @OA\Response(
 *     response=401,
 *     description="Unauthenticated",
 *     @OA\JsonContent(ref="#/components/schemas/UnAuthenticatedResponse")
 *   )
 * )
 */
class RetrieveQuoteRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'api_client_email' => ['required', 'string', 'email'],
            'booking_item' => ['required', 'string', 'uuid'],
        ];
    }
}
