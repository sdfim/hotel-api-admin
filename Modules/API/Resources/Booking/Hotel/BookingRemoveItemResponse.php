<?php

namespace Modules\API\Resources\Booking\Hotel;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="BookingRemoveItemResponse",
 *   title="Booking Remove Item Response",
 *   description="Schema of Booking Remove Item Response",
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
 *         property="booking_id",
 *         type="string",
 *         description="Booking ID (e.g., 'c698abfe-9bfa-45ee-a201-dc7322e008ab').",
 *         example="c698abfe-9bfa-45ee-a201-dc7322e008ab"
 *       ),
 *       @OA\Property(
 *         property="booking_item",
 *         type="string",
 *         description="Booking Item (e.g., 'f8287abe-52be-43a2-8354-b8c4327786a4').",
 *         example="f8287abe-52be-43a2-8354-b8c4327786a4"
 *       ),
 *       @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Status (e.g., 'This item is not in the cart').",
 *         example="This item is not in the cart"
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
 *     example="BookingRemoveItemResponse",
 *     summary="Example of Booking Remove Item Response",
 *     value=
 * {
 * 	"success": true,
 * 	"data": {
 * 		"result": {
 * 			"booking_id": "c698abfe-9bfa-45ee-a201-dc7322e008ab",
 * 			"booking_item": "f8287abe-52be-43a2-8354-b8c4327786a4",
 * 			"status": "This item is not in the cart"
 * 		}
 * 	},
 * 	"message": "success"
 * }
 * )
 */

class BookingRemoveItemResponse
{
}
