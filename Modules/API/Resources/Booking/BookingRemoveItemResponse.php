<?php

namespace Modules\API\Resources\Booking;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="BookingRemoveItemResponse",
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
 *       type="string",
 *       description="Result of the response.",
 *       example="Room cancelled."
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
 *     example="BookingRemoveItemResponse",
 *     summary="Example Booking Remove Item Response",
 *     value=
 * {
 * 	"success": true,
 * 	"data": {
 * 		"result": "Room cancelled."
 * 	},
 * 	"message": "success"
 * }
 * ),
 * @OA\Examples(
 *   example="BookingRemoveItemResponseError",
 *   summary="Example Booking Remove Item Response Error",
 *   value=
 *   {
 *	   "success": false,
 *	   "error": "Room is already cancelled."
 *	 }
 * )
 */

class BookingRemoveItemResponse
{
}
