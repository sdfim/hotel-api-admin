<?php

namespace Modules\API\Resources\Booking\Hotel;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="BookingAddPassengersRequest",
 *   title="Booking Add Passengers Request",
 *   description="Schema Booking Add Passengers Request",
 *   type="object",
 *   required={"passengers"},
 *   @OA\Property(
 *     property="passengers",
 *     type="array",
 *     description="Passengers",
 *     @OA\Items(
 *       type="object",
 *       required={"title", "given_name", "family_name", "date_of_birth"},
 *       @OA\Property(
 *         property="title",
 *         type="string",
 *         description="Title",
 *         example="mr"
 *       ),
 *       @OA\Property(
 *         property="given_name",
 *         type="string",
 *         description="Given Name",
 *         example="John"
 *       ),
 *       @OA\Property(
 *         property="family_name",
 *         type="string",
 *         description="Family Name",
 *         example="Portman"
 *       ),
 *       @OA\Property(
 *         property="date_of_birth",
 *         type="string",
 *         description="Date of Birth",
 *         example="2080-12-14"
 *       )
 *     )
 *   )
 * ),
 * @OA\Examples(
 *     example="BookingAddPassengersRequest",
 *     summary="Example Booking Add Passengers Request without booking_items",
 *     value={
 *       "passengers": {
 *         {
 *           "title": "mr",
 *           "given_name": "John",
 *           "family_name": "Portman",
 *           "date_of_birth": "1988-12-14"
 *         },
 *         {
 *           "title": "ms",
 *           "given_name": "Diana",
 *           "family_name": "Donald",
 *           "date_of_birth": "1980-07-18"
 *         }
 *       }
 *     }
 *   )
 * ),
 * @OA\Schema(
 *   schema="BookingAddPassengersRequestAdvanced",
 *   title="Booking Add Passengers Request Use booking_items",
 *   description="Schema Booking Add Passengers Request Use booking_items",
 *   type="object",
 *   required={"passengers"},
 *   @OA\Property(
 *     property="passengers",
 *     type="array",
 *     description="Passengers",
 *     @OA\Items(
 *       type="object",
 *       required={"title", "given_name", "family_name", "date_of_birth"},
 *       @OA\Property(
 *         property="title",
 *         type="string",
 *         description="Title",
 *         example="mr"
 *       ),
 *       @OA\Property(
 *         property="given_name",
 *         type="string",
 *         description="Given Name",
 *         example="John"
 *       ),
 *       @OA\Property(
 *         property="family_name",
 *         type="string",
 *         description="Family Name",
 *         example="Portman"
 *       ),
 *       @OA\Property(
 *         property="date_of_birth",
 *         type="string",
 *         description="Date of Birth",
 *         example="2080-12-14"
 *       )
 *     )
 *   ),
 *   @OA\Property(
 *     property="booking_items",
 *     type="array",
 *     description="Booking Items",
 *     @OA\Items(
 *       type="string",
 *       description="Booking Item ID"
 *     )
 *   )
 * ),
 * @OA\Examples(
 *     example="BookingAddPassengersRequestAdvanced",
 *     summary="Example Booking Add Passengers Request use booking_items",
 *     value={
 *       "booking_items": {
 *         "bed7af51-836a-4d7e-8f82-8efe8b1825d4",
 *         "50b8b1a0-a07e-4450-b4cc-075742e77be8"
 *       },
 *       "passengers": {
 *         {
 *           "title": "mr",
 *           "given_name": "John",
 *           "family_name": "Portman",
 *           "date_of_birth": "1988-12-14"
 *         },
 *         {
 *           "title": "ms",
 *           "given_name": "Diana",
 *           "family_name": "Donald",
 *           "date_of_birth": "1980-07-18"
 *         }
 *       }
 *     }
 *   )
 *  }
 * ),
 */

class BookingAddPassengersRequest
{
}
