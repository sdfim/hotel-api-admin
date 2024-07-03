<?php

namespace Modules\API\Resources\Booking\Hotel;

/**
 * @OA\Schema(
 *   schema="BookingAddItemResponse",
 *   title="Booking Add Item Response",
 *   description="Schema Booking Add Item Response",
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
 *     property="data",
 *     type="object",
 *     required={"booking_id"},
 *     @OA\Property(
 *       property="booking_id",
 *       type="string",
 *       description="Booking ID (e.g., 'c0e509c2-09cd-4555-8937-73135c2c9b09').",
 *       example="c0e509c2-09cd-4555-8937-73135c2c9b09"
 *     )
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
 *     example="BookingAddItemResponse",
 *     summary="Example Booking Add Item Response",
 *     value=
 * {
 *     "success": true,
 *     "data": {
 *         "booking_id": "c0e509c2-09cd-4555-8937-73135c2c9b09",
 *     },
 *     "message": "success"
 * }
 * )
 */
class BookingAddItemResponse
{
}
