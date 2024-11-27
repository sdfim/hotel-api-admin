<?php

namespace Modules\API\Resources\Booking\Hotel;

/**
 * @OA\Schema(
 *     schema="AvailabilityChangeBookingResponse",
 *     type="object",
 *     @OA\Property(
 *         property="success",
 *         type="boolean",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="data",
 *         type="object",
 *         @OA\Property(
 *             property="query",
 *             ref="#/components/schemas/AvailabilityQuery"
 *         ),
 *         @OA\Property(
 *             property="result",
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/AvailabilityResult")
 *         ),
 *         @OA\Property(
 *             property="change_search_id",
 *             type="string",
 *             example="1f5f3275-abfa-4c9b-856d-feb79179e0fc"
 *         )
 *     ),
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         example="success"
 *     )
 * ),

 *
 * @OA\Schema(
 *     schema="AvailabilityQuery",
 *     type="object",
 *     @OA\Property(property="booking_id", type="string", example="33b663b9-6b44-4698-9453-1cec19eb858f"),
 *     @OA\Property(property="booking_item", type="string", example="290d6b20-aeb1-4e3b-85e5-b2d2816ecd30"),
 *     @OA\Property(property="page", type="integer", example=1),
 *     @OA\Property(property="results_per_page", type="integer", example=10),
 *     @OA\Property(property="checkin", type="string", format="date", example="2025-01-03"),
 *     @OA\Property(property="checkout", type="string", format="date", example="2025-01-05"),
 *     @OA\Property(property="occupancy", type="array", @OA\Items(ref="#/components/schemas/Occupancy")),
 *     @OA\Property(property="type", type="string", example="hotel"),
 *     @OA\Property(property="rating", type="integer", example=2),
 *     @OA\Property(property="supplier", type="string", example="HBSI"),
 *     @OA\Property(property="destination", type="string", example="508")
 * ),

 *
 * @OA\Schema(
 *     schema="Occupancy",
 *     type="object",
 *     @OA\Property(property="adults", type="integer", example=2)
 * ),

 * @OA\Schema(
 *     schema="AvailabilityResult",
 *     type="object",
 *     @OA\Property(property="distance", type="integer", example=0),
 *     @OA\Property(property="giata_hotel_id", type="integer", example=42851280),
 *     @OA\Property(property="rating", type="string", example="0"),
 *     @OA\Property(property="hotel_name", type="string", example=""),
 *     @OA\Property(property="board_basis", type="string", example=""),
 *     @OA\Property(property="supplier", type="string", example="HBSI"),
 *     @OA\Property(property="supplier_hotel_id", type="string", example="51721"),
 *     @OA\Property(property="destination", type="string", example="Cancun"),
 *     @OA\Property(property="meal_plans_available", type="string", example="No Meal"),
 *     @OA\Property(property="lowest_priced_room_group", type="string", example="500"),
 *     @OA\Property(property="pay_at_hotel_available", type="string", example=""),
 *     @OA\Property(property="pay_now_available", type="string", example=""),
 *     @OA\Property(property="non_refundable_rates", type="string", example="2,4"),
 *     @OA\Property(property="refundable_rates", type="string", example="6"),
 *     @OA\Property(property="room_groups", type="array", @OA\Items(ref="#/components/schemas/RoomGroup")),
 *     @OA\Property(property="room_combinations", type="object", additionalProperties={"type":"string"})
 * ),

 *
 * @OA\Schema(
 *     schema="RoomGroup",
 *     type="object",
 *     @OA\Property(property="total_price", type="number", format="float", example=600),
 *     @OA\Property(property="total_tax", type="number", format="float", example=0),
 *     @OA\Property(property="total_fees", type="number", format="float", example=0),
 *     @OA\Property(property="total_net", type="number", format="float", example=600),
 *     @OA\Property(property="markup", type="number", format="float", example=0),
 *     @OA\Property(property="currency", type="string", example="USD"),
 *     @OA\Property(property="pay_now", type="boolean", example=false),
 *     @OA\Property(property="pay_at_hotel", type="boolean", example=false),
 *     @OA\Property(property="non_refundable", type="boolean", example=true),
 *     @OA\Property(property="meal_plan", type="string", example="No Meal"),
 *     @OA\Property(property="rate_id", type="integer", example=1),
 *     @OA\Property(property="rate_description", type="string", example=""),
 *     @OA\Property(property="cancellation_policies", type="array", @OA\Items(ref="#/components/schemas/CancellationPolicy")),
 *     @OA\Property(property="opaque", type="boolean", example=false),
 *     @OA\Property(property="rooms", type="array", @OA\Items(ref="#/components/schemas/Room")),
 *     @OA\Property(property="package_deal", type="boolean", example=false),
 *     @OA\Property(property="penalty_date", type="string", format="date", example="2024-08-07"),
 *     @OA\Property(property="promotions", type="object")
 * ),

 *
 * @OA\Schema(
 *     schema="CancellationPolicy",
 *     type="object",
 *     @OA\Property(property="description", type="string", example="General Cancellation Policy"),
 *     @OA\Property(property="type", type="string", example="General"),
 *     @OA\Property(property="penalty_start_date", type="string", format="date", example="2024-08-07"),
 *     @OA\Property(property="percentage", type="string", example="100"),
 *     @OA\Property(property="amount", type="string", example="100.00")
 * ),

 *
 * @OA\Schema(
 *     schema="Room",
 *     type="object",
 *     @OA\Property(property="capacity", type="object", @OA\Property(property="unknown", type="integer", example=0)),
 *     @OA\Property(property="giata_room_code", type="string", example=""),
 *     @OA\Property(property="giata_room_name", type="string", example=""),
 *     @OA\Property(property="supplier_room_name", type="string", example="Suite"),
 *     @OA\Property(property="per_day_rate_breakdown", type="string", example=""),
 *     @OA\Property(property="supplier_room_id", type="string", example="2-0-0"),
 *     @OA\Property(property="query_package", type="string", example=""),
 *     @OA\Property(property="room_type", type="string", example="Suite"),
 *     @OA\Property(property="room_description", type="string", example=""),
 *     @OA\Property(property="rate_id", type="string", example="1"),
 *     @OA\Property(property="rate_plan_code", type="string", example="Promo"),
 *     @OA\Property(property="rate_name", type="string", example="Promo"),
 *     @OA\Property(property="rate_description", type="string", example=""),
 *     @OA\Property(property="total_price", type="number", format="float", example=600),
 *     @OA\Property(property="total_tax", type="number", format="float", example=0),
 *     @OA\Property(property="total_fees", type="number", format="float", example=0),
 *     @OA\Property(property="total_net", type="number", format="float", example=600),
 *     @OA\Property(property="markup", type="number", format="float", example=0),
 *     @OA\Property(property="currency", type="string", example="USD"),
 *     @OA\Property(property="booking_item", type="string", example="c289a8c7-6f24-4920-86b3-aa65925444eb"),
 *     @OA\Property(property="cancellation_policies", type="array", @OA\Items(ref="#/components/schemas/CancellationPolicy")),
 *     @OA\Property(property="non_refundable", type="boolean", example=true),
 *     @OA\Property(property="meal_plan", type="string", example="No Meal"),
 *     @OA\Property(property="bed_configurations", type="object"),
 *     @OA\Property(property="breakdown", type="object", @OA\Property(property="nightly", type="array", @OA\Items(ref="#/components/schemas/RateBreakdown"))),
 *     @OA\Property(property="package_deal", type="boolean", example=false),
 *     @OA\Property(property="penalty_date", type="string", format="date", example="2024-08-07"),
 *     @OA\Property(property="promotions", type="object")
 * ),

 *
 * @OA\Schema(
 *     schema="RateBreakdown",
 *     type="object",
 *     @OA\Property(property="amount", type="number", format="float", example=272.73),
 *     @OA\Property(property="title", type="string", example="Base Rate"),
 *     @OA\Property(property="type", type="string", example="base_rate")
 * ),

 *
 * @OA\Schema(
 *     schema="AvailabilitySuccessResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="data", type="object", ref="#/components/schemas/AvailabilityData"),
 *     @OA\Property(property="message", type="string", example="success")
 * ),

 *
 * @OA\Schema(
 *     schema="AvailabilityData",
 *     type="object",
 *     @OA\Property(property="query", ref="#/components/schemas/AvailabilityQuery"),
 *     @OA\Property(property="result", type="array", @OA\Items(ref="#/components/schemas/AvailabilityResult")),
 *     @OA\Property(property="change_search_id", type="string", example="1f5f3275-abfa-4c9b-856d-feb79179e0fc")
 * ),

 *
 * @OA\Examples(
 *     example="AvailabilityChangeBookingResponse",
 *     summary="Availability Response",
 *     value={
 *         "success": true,
 *         "data": {
 *             "query": {
 *                 "booking_id": "33b663b9-6b44-4698-9453-1cec19eb858f",
 *                 "booking_item": "290d6b20-aeb1-4e3b-85e5-b2d2816ecd30",
 *                 "page": 1,
 *                 "results_per_page": 10,
 *                 "checkin": "2025-01-03",
 *                 "checkout": "2025-01-05",
 *                 "occupancy": {
 *                     {
 *                         "adults": 2
 *                     }
 *                 },
 *                 "type": "hotel",
 *                 "rating": 2,
 *                 "supplier": "HBSI",
 *                 "destination": "508"
 *             },
 *             "result": {
 *                 {
 *                     "distance": 0,
 *                     "giata_hotel_id": 42851280,
 *                     "rating": "0",
 *                     "hotel_name": "",
 *                     "board_basis": "",
 *                     "supplier": "HBSI",
 *                     "supplier_hotel_id": "51721",
 *                     "destination": "Cancun",
 *                     "meal_plans_available": "No Meal",
 *                     "lowest_priced_room_group": "500",
 *                     "pay_at_hotel_available": "",
 *                     "pay_now_available": "",
 *                     "non_refundable_rates": "2,4",
 *                     "refundable_rates": "6",
 *                     "room_groups": {
 *                         {
 *                             "total_price": 600,
 *                             "total_tax": 0,
 *                             "total_fees": 0,
 *                             "total_net": 600,
 *                             "markup": 0,
 *                             "currency": "USD",
 *                             "pay_now": false,
 *                             "pay_at_hotel": false,
 *                             "non_refundable": true,
 *                             "meal_plan": "No Meal",
 *                             "rate_id": 1,
 *                             "rate_description": "",
 *                             "cancellation_policies": {
 *                                 {
 *                                     "description": "General Cancellation Policy",
 *                                     "type": "General",
 *                                     "penalty_start_date": "2024-08-07",
 *                                     "percentage": "100"
 *                                 }
 *                             },
 *                             "opaque": false,
 *                             "rooms": {
 *                                 {
 *                                     "capacity": {
 *                                         "unknown": 0
 *                                     },
 *                                     "giata_room_code": "",
 *                                     "giata_room_name": "",
 *                                     "supplier_room_name": "Suite",
 *                                     "per_day_rate_breakdown": "",
 *                                     "supplier_room_id": "2-0-0",
 *                                     "query_package": "",
 *                                     "room_type": "Suite",
 *                                     "room_description": "",
 *                                     "rate_id": "1",
 *                                     "rate_plan_code": "Promo",
 *                                     "rate_name": "Promo",
 *                                     "rate_description": "",
 *                                     "total_price": 600,
 *                                     "total_tax": 0,
 *                                     "total_fees": 0,
 *                                     "total_net": 600,
 *                                     "markup": 0,
 *                                     "currency": "USD",
 *                                     "booking_item": "c289a8c7-6f24-4920-86b3-aa65925444eb",
 *                                     "cancellation_policies": {
 *                                         {
 *                                             "description": "General Cancellation Policy",
 *                                             "type": "General",
 *                                             "penalty_start_date": "2024-08-07",
 *                                             "percentage": "100"
 *                                         }
 *                                     },
 *                                     "non_refundable": true,
 *                                     "meal_plan": "No Meal",
 *                                     "bed_configurations": {},
 *                                     "breakdown": {
 *                                         "nightly": {
 *                                             {
 *                                                 "amount": 272.73,
 *                                                 "title": "Base Rate",
 *                                                 "type": "base_rate"
 *                                             },
 *                                             {
 *                                                 "type": "tax",
 *                                                 "amount": "27.27",
 *                                                 "title": "Occupancy Tax"
 *                                             }
 *                                         },
 *                                         "stay": {},
 *                                         "fees": {}
 *                                     },
 *                                     "package_deal": false,
 *                                     "penalty_date": "2024-08-07",
 *                                     "promotions": {}
 *                                 }
 *                             }
 *                         },
 *                         {
 *                             "total_price": 500,
 *                             "total_tax": 0,
 *                             "total_fees": 0,
 *                             "total_net": 500,
 *                             "markup": 0,
 *                             "currency": "USD",
 *                             "pay_now": false,
 *                             "pay_at_hotel": false,
 *                             "non_refundable": true,
 *                             "meal_plan": "No Meal",
 *                             "rate_id": 2,
 *                             "rate_description": "",
 *                             "cancellation_policies": {
 *                                 {
 *                                     "description": "Early check-out penalty.",
 *                                     "type": "General",
 *                                     "percentage": "20",
 *                                     "amount": "100.00"
 *                                 },
 *                                 {
 *                                     "description": "General Cancellation Policy",
 *                                     "type": "General",
 *                                     "penalty_start_date": "2024-08-07",
 *                                     "percentage": "100"
 *                                 }
 *                             },
 *                             "opaque": false,
 *                             "rooms": {
 *                                 {
 *                                     "capacity": {
 *                                         "unknown": 0
 *                                     },
 *                                     "giata_room_code": "",
 *                                     "giata_room_name": "",
 *                                     "supplier_room_name": "Double",
 *                                     "per_day_rate_breakdown": "",
 *                                     "supplier_room_id": "2-0-0",
 *                                     "query_package": "",
 *                                     "room_type": "Double",
 *                                     "room_description": "",
 *                                     "rate_id": "2",
 *                                     "rate_plan_code": "Promo",
 *                                     "rate_name": "Promo",
 *                                     "rate_description": "",
 *                                     "total_price": 500,
 *                                     "total_tax": 0,
 *                                     "total_fees": 0,
 *                                     "total_net": 500,
 *                                     "markup": 0,
 *                                     "currency": "USD",
 *                                     "booking_item": "cc0866a7-9852-4157-9b13-fc4d8af91acc",
 *                                     "cancellation_policies": {
 *                                         {
 *                                             "description": "Early check-out penalty.",
 *                                             "type": "General",
 *                                             "percentage": "20",
 *                                             "amount": "100.00"
 *                                         },
 *                                         {
 *                                             "description": "General Cancellation Policy",
 *                                             "type": "General",
 *                                             "penalty_start_date": "2024-08-07",
 *                                             "percentage": "100"
 *                                         }
 *                                     },
 *                                     "non_refundable": true,
 *                                     "meal_plan": "No Meal",
 *                                     "bed_configurations": {},
 *                                     "breakdown": {
 *                                         "nightly": {
 *                                             {
 *                                                 "amount": 227.27,
 *                                                 "title": "Base Rate",
 *                                                 "type": "base_rate"
 *                                             },
 *                                             {
 *                                                 "type": "tax",
 *                                                 "amount": "22.73",
 *                                                 "title": "Occupancy Tax"
 *                                             }
 *                                         },
 *                                         "stay": {},
 *                                         "fees": {}
 *                                     },
 *                                     "package_deal": false,
 *                                     "penalty_date": "2024-08-07",
 *                                     "promotions": {}
 *                                 }
 *                             }
 *                         }
 *                     },
 *                     "room_combinations": {
 *                         "c289a8c7-6f24-4920-86b3-aa65925444eb": {
 *                             "c289a8c7-6f24-4920-86b3-aa65925444eb"
 *                         },
 *                         "cd589fbd-6ef7-426e-989e-ddc0feebf6e1": {
 *                             "cd589fbd-6ef7-426e-989e-ddc0feebf6e1"
 *                         },
 *                         "6545505b-b14e-40df-ba9f-b9c2c1c52259": {
 *                             "6545505b-b14e-40df-ba9f-b9c2c1c52259"
 *                         },
 *                         "cc0866a7-9852-4157-9b13-fc4d8af91acc": {
 *                             "cc0866a7-9852-4157-9b13-fc4d8af91acc"
 *                         },
 *                         "4698c3ac-1eae-49c8-a55f-07b8fc5172d6": {
 *                             "4698c3ac-1eae-49c8-a55f-07b8fc5172d6"
 *                         },
 *                         "a8484402-1558-4662-9e6d-57b6de02c24d": {
 *                             "a8484402-1558-4662-9e6d-57b6de02c24d"
 *                         },
 *                         "3edfc0a5-54e6-4e36-8c7b-2c5aef4d5e51": {
 *                             "3edfc0a5-54e6-4e36-8c7b-2c5aef4d5e51"
 *                         },
 *                         "e0d827a7-32ae-44ff-bf6f-7ccfa40ab426": {
 *                             "e0d827a7-32ae-44ff-bf6f-7ccfa40ab426"
 *                         },
 *                         "93d4fbf7-d724-45c8-af6d-0b3d6a7a3b24": {
 *                             "93d4fbf7-d724-45c8-af6d-0b3d6a7a3b24"
 *                         }
 *                     }
 *                 }
 *             },
 *             "change_search_id": "1f5f3275-abfa-4c9b-856d-feb79179e0fc"
 *         },
 *         "message": "success"
 *     }
 * )
 */
class AvailabilityChangeBookingResponse
{
}
