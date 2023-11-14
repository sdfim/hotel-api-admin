<?php

namespace Modules\API\Resources\Booking;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="BookingAddPassengersResponse",
 *   title="Booking Add Passengers Response",
 *   description="Schema Booking Add Passengers Response",
 *   type="object",
 *   required={"success", "data", "message"},
 *   @OA\Property(
 *     property="success",
 *     type="boolean",
 *     description="Success status",
 *     example=true
 *     
 *     ),
 *     @OA\Property(
 *       property="result",
 *       type="object",
 *       description="Result object",
 *       @OA\Property(
 *         property="booking_id",
 *         type="string",
 *         description="Booking ID",
 *         example="5abcab52-00b0-423c-aafe-2fed6f6d1f4e"
 *       ),
 *       @OA\Property(
 *         property="booking_item",
 *         type="string",
 *         description="Booking Item",
 *         example="bed7af51-836a-4d7e-8f82-8efe8b1825d4"
 *       ),
 *       @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Status",
 *         example="Passengers added to booking."
 *       )
 *     )
 *   ),
 *   @OA\Property(
 *     property="message",
 *     type="string",
 *     description="Message",
 *     example="success"
 *   )
 * ),
 * @OA\Examples(
 *   example="BookingAddPassengersResponseUpdate",
 *   summary="Example Booking Add Passengers Response Update",
 *   value=
 *   {
 * 	"success": true,
 * 	"data": {
 * 		"result": {
 * 			"booking_id": "5abcab52-00b0-423c-aafe-2fed6f6d1f4e",
 * 			"booking_item": "bed7af51-836a-4d7e-8f82-8efe8b1825d4",
 * 			"status": "Passengers updated to booking."
 * 		}
 * 	},
 * 	"message": "success"
 * }
 * )
 * @OA\Examples(
 *     example="BookingAddPassengersResponseAdd",
 *     summary="Example Booking Add Passengers Response",
 *     value=
 * {
 * 	"success": true,
 * 	"data": {
 * 		"result": {
 * 			"booking_id": "5abcab52-00b0-423c-aafe-2fed6f6d1f4e",
 * 			"booking_item": "bed7af51-836a-4d7e-8f82-8efe8b1825d4",
 * 			"status": "Passengers added to booking."
 * 		}
 * 	},
 * 	"message": "success"
 * }
 * ),
 * @OA\Examples(
 *   example="BookingAddPassengersResponseError",
 *   summary="Example Booking Add Passengers Response Error",
 *   value=
 * {
 *     "success": false,
 *     "error": {
 *         "booking_id": "b1b7db09-6b53-480e-bece-53a2d5ef30d6",
 *         "booking_item": "84fb55f2-5792-4958-b475-4c39ea787b5f",
 *         "status": "The number of rooms does not match the number of rooms in the search. Must be 2 rooms."
 *     }
 * }
 * )
 */

class BookingAddPassengersResponse
{
}
