<?php

namespace Modules\API\Resources\Booking\Hotel;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="BookingAddPassengersRequest",
 *   title="Booking Add Passengers Request",
 *   description="Schema Booking Add Passengers Reques",
 *   type="object",
 *   required={"rooms"},
 *   @OA\Property(
 *     property="rooms",
 *     type="array",
 *     description="Rooms",
 *     @OA\Items(
 *       type="object",
 *       required={"title", "given_name", "family_name", "date_of_birth"},
 *       @OA\Property(
 *         property="title",
 *         type="string",
 *         description="Title",
 *         example="mr"
 *         ),
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
 *     summary="Example Booking Add Passengers Request Hotel",
 *     value=
 * { 
 *    "rooms": {
 *      {
 *        "title": "mr",
 *        "given_name": "John",
 *        "family_name": "Portman",
 *        "date_of_birth": "1988-12-14"
 *      },
 *      {
 *        "title": "mr",
 *        "given_name": "Diana",
 *        "family_name": "Donald",
 *        "date_of_birth": "1980-07-18"
 *      }
 *    }
 *  }
 * )
 */

class BookingAddPassengersRequest
{
}
