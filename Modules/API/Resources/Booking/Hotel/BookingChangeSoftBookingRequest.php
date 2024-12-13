<?php

namespace Modules\API\Resources\Booking\Hotel;

/**
 * @OA\Schema(
 *     schema="BookingChangeSoftBookingRequest",
 *     title="Booking Change Soft Request",
 *     description="Soft schema for booking change requests",
 *     required={"booking_id", "booking_item", "query"},
 *     @OA\Property(
 *         property="booking_id",
 *         type="string",
 *         description="Booking ID",
 *         example="3333cee5-b4a3-4e51-bfb0-02d09370b585"
 *     ),
 *     @OA\Property(
 *         property="booking_item",
 *         type="string",
 *         description="Booking item",
 *         example="c7bb44c1-bfaa-4d05-b2f8-37541b454f8c"
 *     ),
 *     @OA\Property(
 *         property="query",
 *         type="object",
 *         required={"given_name", "family_name"},
 *         @OA\Property(
 *             property="given_name",
 *             type="string",
 *             description="Given Name (e.g., 'John').",
 *             example="John"
 *         ),
 *         @OA\Property(
 *             property="family_name",
 *             type="string",
 *             description="Family Name (e.g., 'Smit').",
 *             example="Smit"
 *         ),
 *         @OA\Property(
 *             property="smoking",
 *             type="boolean",
 *             description="Smoking (e.g., 'false').",
 *             example="false"
 *         ),
 *         @OA\Property(
 *             property="special_request",
 *             type="string",
 *             description="Special Request (e.g., 'Top floor or away from street please').",
 *             example="Top floor or away from street please"
 *         ),
 *         @OA\Property(
 *             property="loyalty_id",
 *             type="string",
 *             description="Loyalty ID (e.g., 'ABC123').",
 *             example="ABC123"
 *         ),
 *         @OA\Property(
 *             property="passengers",
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 required={"title", "given_name", "family_name", "date_of_birth"},
 *                 @OA\Property(
 *                     property="title",
 *                     type="string",
 *                     example="Mr"
 *                 ),
 *                 @OA\Property(
 *                     property="given_name",
 *                     type="string",
 *                     example="John"
 *                 ),
 *                 @OA\Property(
 *                     property="family_name",
 *                     type="string",
 *                     example="Doe"
 *                 ),
 *                 @OA\Property(
 *                     property="date_of_birth",
 *                     type="string",
 *                     example="1990-01-01"
 *                 ),
 *                 @OA\Property(
 *                     property="room",
 *                     type="integer",
 *                     example=1
 *                 )
 *             )
 *         )
 *     )
 * )
 */
class BookingChangeSoftBookingRequest
{
}
