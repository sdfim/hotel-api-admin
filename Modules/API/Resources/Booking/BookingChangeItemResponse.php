<?php

namespace Modules\API\Resources\Booking;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="BookingChangeItemResponse",
 *   title="Booking Change Item Response",
 *   description="Schema Booking Change Item Response",
 *   type="object",
 *   required={"success", "data", "message"},
 *   @OA\Property(
 *     property="success",
 *     type="boolean",
 *     description="Success (e.g., 'true').",
 *     example="true"
 *   ),
 *   @OA\Property(
 *     property="message",
 *     type="string",
 *     description="Message (e.g., 'success').",
 *     example="success"
 *   )
 * ),
 * @OA\Examples(
 *     example="BookingChangeItemResponse",
 *     summary="Example Booking Change Item Response",
 *     value=
 * {
 *     "success": true,
 *     "message": "success"
 * }
 * ),
 * @OA\Examples(
 *   example="BookingChangeItemResponseError",
 *   summary="Example Booking Change Item Response Error",
 *   value=
 * {
 *   "success": false,
 *   "error": {
 *     {
 *       "type": "no_change",
 *       "message": "No change possible. Reservation already matches requested values."
 *     }
 *   },
 *   "message": "An invalid request was sent in, please check the nested errors for details."
 * }
 * )
 */

class BookingChangeItemResponse
{
}
