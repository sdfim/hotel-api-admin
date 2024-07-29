<?php

namespace Modules\API\Resources\Booking\Hotel;

/**
 * @OA\Schema(
 *   schema="BookingChangeSoftBookingResponse",
 *   title="Booking Change Booking Response",
 *   description="Schema Booking Change Booking Response",
 *   type="object",
 *   required={"success", "data", "message"},
 *
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
 *
 * @OA\Examples(
 *     example="BookingChangeSoftBookingResponse",
 *     summary="Example Booking Change Booking Response",
 *     value=
 * {
 *     "success": true,
 *     "message": "success"
 * }
 * ),
 * @OA\Examples(
 *   example="BookingChangeSoftBookingResponseError",
 *   summary="Example Booking Change Booking Response Error",
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
class BookingChangeSoftBookingResponse
{
}
