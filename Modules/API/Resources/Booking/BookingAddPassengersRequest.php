<?php

namespace Modules\API\Resources\Booking;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="BookingAddPassengersRequest",
 *   title="Booking Add Passengers Request",
 *   description="Schema Booking Add Passengers Reques",
 *   type="object",
 *   required={"query"},
 *   @OA\Property(
 *     property="email",
 *     type="string",
 *     example="john@example.com",
 *     description="Email"
 *   ),
 *   @OA\Property(
 *     property="phone",
 *     type="object",
 *     description="Phone",
 *     @OA\Property(
 *       property="country_code",
 *       type="string",
 *       example="1",
 *       description="Country Code"
 *     ),
 *     @OA\Property(
 *       property="area_code",
 *       type="string",
 *       example="487",
 *       description="Area Code"
 *     ),
 *     @OA\Property(
 *       property="number",
 *       type="string",
 *       example="5550077",
 *       description="Number"
 *     )
 *   ),
 *   @OA\Property(
 *     property="rooms",
 *     type="array",
 *     description="Rooms",
 *     @OA\Items(
 *       type="object",
 *       @OA\Property(
 *         property="given_name",
 *         type="string",
 *         example="John",
 *         description="Given Name"
 *         ),
 *         @OA\Property(
 *           property="family_name",
 *           type="string",
 *           example="Portman",
 *           description="Family Name"
 *         )
 *       )
 *     )
 *   ),
 *   @OA\Property(
 *     property="payments",
 *     type="array",
 *     description="Payments",
 *     @OA\Items(
 *       type="object",
 *       @OA\Property(
 *         property="billing_contact",
 *         type="object",
 *         description="Billing Contact",
 *         @OA\Property(
 *           property="given_name",
 *           type="string",
 *           example="John",
 *           description="Given Name"
 *         ),
 *         @OA\Property(
 *           property="family_name",
 *           type="string",
 *           example="Smith",
 *           description="Family Name"
 *         ),
 *         @OA\Property(
 *           property="address",
 *           type="object",
 *           description="Address",
 *           @OA\Property(
 *             property="line_1",
 *             type="string",
 *             example="555 1st St",
 *             description="Line 1"
 *           ),
 *           @OA\Property(
 *             property="city",
 *             type="string",
 *             example="Seattle",
 *             description="City"
 *           ),
 *           @OA\Property(
 *             property="state_province_code",
 *             type="string",
 *             example="WA",
 *             description="State Province Code"
 *           ),
 *           @OA\Property(
 *             property="postal_code",
 *             type="string",
 *             example="98121",
 *             description="Postal Code"
 *           ),
 *           @OA\Property(
 *             property="country_code",
 *             type="string",
 *             example="US",
 *             description="Country Code"
 *           )
 *         )
 *       )
 *     )
 *   )
 * ),    
 * @OA\Examples(
 *     example="BookingAddPassengersRequest",
 *     summary="Example Booking Add Passengers Request",
 *     value=
 * {
 *    "email": "john@example.com",
 *    "phone": {
 *       "country_code": "1",
 *       "area_code": "487",
 *       "number": "5550077"
 *    },
 *    "rooms": {
 *       {
 *          "given_name": "John",
 *          "family_name": "Portman"
 *       }
 *    },
 *    "payments": {
 *       {
 *          "billing_contact": {
 *             "given_name": "John",
 *             "family_name": "Smith",
 *             "address": {
 *                "line_1": "555 1st St",
 *                "city": "Seattle",
 *                "state_province_code": "WA",
 *                "postal_code": "98121",
 *                "country_code": "US"
 *             }
 *          }
 *       }
 *    }
 * }
 * )
 */

class BookingAddPassengersRequest
{
}
