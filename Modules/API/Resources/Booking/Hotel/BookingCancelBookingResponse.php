<?php

namespace Modules\API\Resources\Booking\Hotel;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="BookingCancelBookingResponse",
 *   title="Booking Remove Item Response",
 *   description="Schema Booking Remove Item Response",
 *   type="object",
 *   required={"success", "data", "message"},
 *   @OA\Property(
 *     property="success",
 *     type="boolean",
 *     description="Indicates the success status of the response.",
 *     example=true
 *   ),
 *   @OA\Property(
 *     property="data",
 *     type="object",
 *     description="Data of the response.",
 *     @OA\Property(
 *       property="result",
 *       type="array",
 *       description="Result of the response.",
 *       @OA\Items(
 *         type="object",
 *         @OA\Property(
 *           property="booking_item",
 *           type="string",
 *           description="Booking item.",
 *           example="c7bb44c1-bfaa-4d05-b2f8-37541b454f8c"
 *         ),
 *         @OA\Property(
 *           property="status",
 *           type="string",
 *           description="Status of the response.",
 *           example="Room is already cancelled."
 *         )
 *       )
 *     )
 *   ),
 *   @OA\Property(
 *     property="message",
 *     type="string",
 *     description="Message of the response.",
 *     example="success"
 *   )
 * ),
 * @OA\Examples(
 *     example="BookingCancelBookingResponse",
 *     summary="Example Booking Remove Item Response",
 *     value=
 * {
 * 	"success": true,
 * 	"data": {
 * 		"result": {
 * 			{
 * 				"booking_item": "c7bb44c1-bfaa-4d05-b2f8-37541b454f8c",
 * 				"status": "Room is already cancelled."
 * 			}
 * 		}
 * 	},
 * 	"message": "success"
 * }
 * )
 */

class BookingCancelBookingResponse
{
}
