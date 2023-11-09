<?php

namespace Modules\API\Resources\Booking;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="BookingAddPassengersRequest",
 *   title="Booking Add Passengers Request",
 *   description="Schema Booking Add Passengers Reques",
 *   type="object",
 *   required={"title", "first_name", "last_name", "rooms"},
 *   @OA\Property(
 *     property="title",
 *     type="string",
 *     example="mr"
 *   ),
 *   @OA\Property(
 *     property="first_name",
 *     type="string",
 *     example="John"
 *   ),
 *   @OA\Property(
 *     property="last_name",
 *     type="string",
 *     example="Portman"
 *   ),
 *   @OA\Property(
 *     property="rooms",
 *     type="array",
 *     @OA\Items(
 *       type="object",
 *       required={"given_name", "family_name"},
 *       @OA\Property(
 *         property="given_name",
 *         type="string",
 *         example="John"
 *       ),
 *       @OA\Property(
 *         property="family_name",
 *         type="string",
 *         example="Portman"
 *       ),
 *       @OA\Property(
 *         property="date_birth_children",
 *         type="string",
 *         example="2010-01-01"
 *       )
 *     )
 *   )
 * ),
 * @OA\Examples(
 *     example="BookingAddPassengersRequest",
 *     summary="Example Booking Add Passengers Request",
 *     value=
 * {
 *   "title": "mr",
 *   "first_name": "John",
 *   "last_name": "Portman",
 *   "rooms": {
 *     {
 *       "given_name": "John",
 *       "family_name": "Portman"
 *     },
 *     {
 *       "given_name": "John",
 *       "family_name": "Portman"
 *     }
 *   }
 * }
 * )
 */

class BookingAddPassengersRequest
{
}
