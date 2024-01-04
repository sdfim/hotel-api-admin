<?php

namespace Modules\API\Resources\Booking\Hotel;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="BookingBookResponse",
 *   title="Booking Book Response",
 *   description="Schema of Booking Book Response",
 *   type="object",
 *   required={"success", "data", "message"},
 *   @OA\Property(
 *     property="success",
 *     type="boolean",
 *     description="Success (e.g., 'true').",
 *     example="true"
 *   ),
 *   @OA\Property(
 *     property="data",
 *     type="object",
 *     description="Data",
 *     @OA\Property(
 *       property="result",
 *       type="object",
 *       description="Result",
 *       @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Status (e.g., 'booked').",
 *         example="booked"
 *       ),
 *       @OA\Property(
 *         property="booking_id",
 *         type="string",
 *         description="Booking ID (e.g., 'c698abfe-9bfa-45ee-a201-dc7322e008ab').",
 *         example="c698abfe-9bfa-45ee-a201-dc7322e008ab"
 *       ),
 *       @OA\Property(
 *         property="search_id",
 *         type="string",
 *         description="Search ID (e.g., 'd2ff0669-8d0e-4709-826d-ccdcef01d4a2').",
 *         example="d2ff0669-8d0e-4709-826d-ccdcef01d4a2"
 *       ),
 *       @OA\Property(
 *         property="booking_item",
 *         type="string",
 *         description="Booking Item",
 *         example="f8287abe-52be-43a2-8354-b8c4327786a4"
 *       ),
 *       @OA\Property(
 *         property="supplier",
 *         type="string",
 *         description="Supplier (e.g., 'Expedia').",
 *         example="Expedia"
 *       ),
 *       @OA\Property(
 *         property="hotel_name",
 *         type="string",
 *         description="Hotel Name (e.g., 'Sheraton New York Times Square Hotel (60295986)').",
 *         example="Sheraton New York Times Square Hotel (60295986)"
 *       ),
 *       @OA\Property(
 *         property="rooms",
 *         type="object",
 *         description="Rooms",
 *         @OA\Property(
 *           property="room_name",
 *           type="string",
 *           description="Room Name (e.g., 'Superior Room, 2 Double Beds, Non Smoking (High Floor)').",
 *           example="Superior Room, 2 Double Beds, Non Smoking (High Floor)"
 *         ),
 *         @OA\Property(
 *           property="meal_plan",
 *           type="string",
 *           description="Meal Plan (e.g., '').",
 *           example=""
 *         )
 *       ),
 *       @OA\Property(
 *         property="cancellation_terms",
 *         type="string",
 *         description="Cancellation Terms (e.g., '').",
 *         example=""
 *       ),
 *       @OA\Property(
 *         property="rate",
 *         type="string",
 *         description="Rate (e.g., '274355054').",
 *         example="274355054"
 *       ),
 *       @OA\Property(
 *         property="total_price",
 *         type="number",
 *         description="Total Price (e.g., 1287.02).",
 *         example=1287.02
 *       ),
 *       @OA\Property(
 *         property="total_tax",
 *         type="number",
 *         description="Total Tax (e.g., 176.82).",
 *         example=176.82
 *       ),
 *       @OA\Property(
 *         property="total_fees",
 *         type="number",
 *         description="Total Fees (e.g., 120).",
 *         example=120
 *       ),
 *       @OA\Property(
 *         property="total_net",
 *         type="number",
 *         description="Total Net (e.g., 1110.2).",
 *         example=1110.2
 *       ),
 *       @OA\Property(
 *         property="affiliate_service_charge",
 *         type="number",
 *         description="Affiliate Service Charge (e.g., 988.08).",
 *         example=988.08
 *       ),
 *       @OA\Property(
 *         property="currency",
 *         type="string",
 *         description="Currency (e.g., 'EUR').",
 *         example="EUR"
 *       ),
 *       @OA\Property(
 *         property="per_night_breakdown",
 *         type="number",
 *         description="Per Night Breakdown (e.g., 643.51).",
 *         example=643.51
 *       ),
 *       @OA\Property(
 *         property="links",
 *         type="object",
 *         description="Links",
 *         @OA\Property(
 *           property="remove",
 *           type="object",
 *           description="Remove",
 *           @OA\Property(
 *             property="method",
 *             type="string",
 *             description="Method (e.g., 'DELETE').",
 *             example="DELETE"
 *           ),
 *           @OA\Property(
 *             property="href",
 *             type="string",
 *             description="Href (e.g., '/api/booking/cancel-booking?booking_id=c698abfe-9bfa-45ee-a201-dc7322e008ab&booking_item=f8287abe-52be-43a2-8354-b8c4327786a4').",
 *             example="/api/booking/cancel-booking?booking_id=c698abfe-9bfa-45ee-a201-dc7322e008ab&booking_item=f8287abe-52be-43a2-8354-b8c4327786a4"
 *           )
 *         ),
 *         @OA\Property(
 *           property="change",
 *           type="object",
 *           description="Change",
 *           @OA\Property(
 *             property="method",
 *             type="string",
 *             description="Method (e.g., 'PUT').",
 *             example="PUT"
 *           ),
 *           @OA\Property(
 *             property="href",
 *             type="string",
 *             description="Href (e.g., '/api/booking/change-booking?booking_id=c698abfe-9bfa-45ee-a201-dc7322e008ab&booking_item=f8287abe-52be-43a2-8354-b8c4327786a4').",
 *             example="/api/booking/change-booking?booking_id=c698abfe-9bfa-45ee-a201-dc7322e008ab&booking_item=f8287abe-52be-43a2-8354-b8c4327786a4"
 *           )
 *         ),
 *         @OA\Property(
 *           property="retrieve",
 *           type="object",
 *           description="Retrieve",
 *           @OA\Property(
 *             property="method",
 *             type="string",
 *             description="Method (e.g., 'GET').",
 *             example="GET"
 *           ),
 *           @OA\Property(
 *             property="href",
 *             type="string",
 *             description="Href (e.g., '/api/booking/retrieve-booking?booking_id=c698abfe-9bfa-45ee-a201-dc7322e008ab').",
 *             example="/api/booking/retrieve-booking?booking_id=c698abfe-9bfa-45ee-a201-dc7322e008ab"
 *           )
 *         )
 *       )
 *     )
 *   ),
 *   @OA\Property(
 *     property="message",
 *     type="string",
 *     description="Message (e.g., 'success').",
 *     example="success"
 *   )
 * ),
 * @OA\Examples(
 *     example="BookingBookResponse",
 *     summary="Example of Booking Book Response",
 *     value=
 * {
 *    "success": true,
 *    "data": {
 *        {
 *            "status": "booked",
 *            "booking_id": "b1b7db09-6b53-480e-bece-53a2d5ef30d6",
 *            "booking_item": "84fb55f2-5792-4958-b475-4c39ea787b5f",
 *            "supplier": "Expedia",
 *            "hotel_name": "Sheraton New York Times Square Hotel (60295986)",
 *            "rooms": {
 *                "room_name": "Superior Room, 2 Double Beds, Non Smoking (High Floor)",
 *                "meal_plan": ""
 *            },
 *            "cancellation_terms": "",
 *            "rate": "274355054",
 *            "total_price": 1287.02,
 *            "total_tax": 176.82,
 *            "total_fees": 120,
 *            "total_net": 1110.2,
 *            "affiliate_service_charge": 988.08,
 *            "currency": "EUR",
 *            "per_night_breakdown": 643.51,
 *            "links": {
 *                "remove": {
 *                    "method": "DELETE",
 *                    "href": "\/api\/booking\/cancel-booking?booking_id=b1b7db09-6b53-480e-bece-53a2d5ef30d6&booking_item=84fb55f2-5792-4958-b475-4c39ea787b5f"
 *                },
 *                "change": {
 *                    "method": "PUT",
 *                    "href": "\/api\/booking\/change-booking?booking_id=b1b7db09-6b53-480e-bece-53a2d5ef30d6&booking_item=84fb55f2-5792-4958-b475-4c39ea787b5f"
 *                },
 *                "retrieve": {
 *                    "method": "GET",
 *                    "href": "\/api\/booking\/retrieve-booking?booking_id=b1b7db09-6b53-480e-bece-53a2d5ef30d6"
 *                }
 *            }
 *        }
 *    },
 *    "message": "success"
 * }
 * ),
 * @OA\Schema(
 *   schema="BookingBookResponseErrorItem",
 *   title="Booking Book Response Error Item",
 *   description="Schema of Booking Book Response Error Item",
 *   type="object",
 *   required={"message"},
 *   @OA\Property(
 *     property="message",
 *     type="string",
 *     description="Message (e.g., 'Invalid booking_id').",
 *     example="Invalid booking_id"
 *   )
 * ),
 * @OA\Examples(
 *     example="BookingBookResponseErrorItem",
 *     summary="Example of Booking Book Response Error Item",
 *     value=
 * {
 *     "message": "Invalid booking_id"
 * }
 * ),
 * @OA\Examples(
 *     example="BookingBookResponseErrorBooked",
 *     summary="Example of Booking Book Response Error Booked",
 *     value=
 * {
 *     "success": false,
 *     "error": {
 *         "error": "No items to book OR the order cart (booking_id) is complete/booked"
 *     },
 *     "message": "failed"
 * }
 * )
 */
class BookingBookResponse
{
}
