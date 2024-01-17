<?php

namespace Modules\API\Resources\Booking\Hotel;

/**
 * @OA\Schema(
 *   schema="BookingChangeBookingRequest",
 *   title="Booking Change Booking Request",
 *   description="Schema Booking Add Booking Request",
 *   type="object",
 *   required={"query"},
 *   @OA\Property(
 *     property="query",
 *     type="object",
 *     required={"given_name", "family_name"},
 *     @OA\Property(
 *       property="given_name",
 *       type="string",
 *       description="Given Name (e.g., 'John').",
 *       example="John"
 *     ),
 *     @OA\Property(
 *       property="family_name",
 *       type="string",
 *       description="Family Name (e.g., 'Smit').",
 *       example="Smit"
 *     ),
 *     @OA\Property(
 *       property="smoking",
 *       type="boolean",
 *       description="Smoking (e.g., 'false').",
 *       example="false"
 *     ),
 *     @OA\Property(
 *       property="special_request",
 *       type="string",
 *       description="Special Request (e.g., 'Top floor or away frostreet please').",
 *       example="Top floor or away frostreet please"
 *     ),
 *     @OA\Property(
 *       property="loyalty_id",
 *       type="string",
 *       description="Loyalty ID (e.g., 'ABC123').",
 *       example="ABC123"
 *     )
 *   )
 * ),
 * @OA\Examples(
 *     example="BookingChangeBookingRequest",
 *     summary="Example Booking Change Booking Request",
 *     value=
 *        {
 *            "query":
 *            {
 *                "given_name": "John",
 *                "family_name": "Smit",
 *                "smoking": false,
 *                "special_request": "Top floor or away frostreet please",
 *                "loyalty_id": "ABC123"
 *            }
 *        }
 * )
 */
class BookingChangeBookingRequest
{
}
