<?php

namespace Modules\API\Resources\Booking;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="BookingBookRequest",
 *   title="Booking Book Request",
 *   description="Schema Booking Book Request",
 *   type="object",
 *   required={"amount_pay", "email", "phone", "booking_contact"},
 *   @OA\Property(
 *     property="amount_pay",
 *     type="string",
 *     example="Deposit"
 *   ),
 *   @OA\Property(
 *     property="email",
 *     type="string",
 *     example="john@example.com",
 *     description="Email"
 *   ),
 *   @OA\Property(
 *     property="phone",
 *     type="object",
 *     required={"country_code", "area_code", "number"},
 *     @OA\Property(
 *       property="country_code",
 *       type="string",
 *       example="1"
 *     ),
 *     @OA\Property(
 *       property="area_code",
 *       type="string",
 *       example="487"
 *     ),
 *     @OA\Property(
 *       property="number",
 *       type="string",
 *       example="5550077"
 *     )
 *   ),
 *   @OA\Property(
 *     property="booking_contact",
 *     type="object",
 *     required={"given_name", "family_name", "address"},
 *     @OA\Property(
 *       property="given_name",
 *       type="string",
 *       example="John"
 *     ),
 *     @OA\Property(
 *       property="family_name",
 *       type="string",
 *       example="Smith"
 *     ),
 *     @OA\Property(
 *       property="address",
 *       type="object",
 *       required={"line_1", "city", "state_province_code", "postal_code", "country_code"},
 *       @OA\Property(
 *         property="line_1",
 *         type="string",
 *         example="555 1st St"
 *       ),
 *       @OA\Property(
 *         property="city",
 *         type="string",
 *         example="Seattle"
 *       ),
 *       @OA\Property(
 *         property="state_province_code",
 *         type="string",
 *         example="WA"
 *       ),
 *       @OA\Property(
 *         property="postal_code",
 *         type="string",
 *         example="98121"
 *       ),
 *       @OA\Property(
 *         property="country_code",
 *         type="string",
 *         example="US"
 *       )
 *     )
 *   )
 * ),       
 * @OA\Examples(
 *     example="BookingBookRequest",
 *     summary="Example Booking Book Request",
 *     value=
 * 		{
 * 		   "amount_pay":"Deposit",
 * 		   "email":"john@example.com",
 * 		   "phone":{
 * 		      "country_code":"1",
 * 		      "area_code":"487",
 * 		      "number":"5550077"
 * 		   },
 * 		   "booking_contact":{
 * 		      "given_name":"John",
 * 		      "family_name":"Smith",
 * 		      "address":{
 * 		         "line_1":"555 1st St",
 * 		         "city":"Seattle",
 * 		         "state_province_code":"WA",
 * 		         "postal_code":"98121",
 * 		         "country_code":"US"
 * 		      }
 * 		   }
 * 		}
 * )
 */

class BookingBookRequest
{
}
