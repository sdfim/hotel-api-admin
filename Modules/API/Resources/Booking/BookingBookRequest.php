<?php

namespace Modules\API\Resources\Booking;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="BookingBookRequest",
 *   title="Booking Book Request",
 *   description="Schema Booking Book Request",
 *   type="object",
 *   required={"query"},
 *   @OA\Property(
 *     property="query",
 *     type="object",
 *     required={"hold", "email", "phone", "rooms", "payments"},
 *     @OA\Property(
 *       property="hold",
 *       type="boolean",
 *       description="Hold booking (e.g., 'false').",
 *       example="false"
 *     ),
 *     @OA\Property(
 *       property="email",
 *       type="string",
 *       description="Email of the customer (e.g., 'john@example.com').",
 *       example="john@example.com",
 *       format="email"
 *     ),
 *     @OA\Property(
 *       property="phone",
 *       type="object",
 *       required={"country_code", "area_code", "number"},
 *       @OA\Property(
 *         property="country_code",
 *         type="string",
 *         description="Country code of the phone number (e.g., '1').",
 *         example="1"
 *       ),
 *       @OA\Property(
 *         property="area_code",
 *         type="string",
 *         description="Area code of the phone number (e.g., '487').",
 *         example="487"
 *       ),
 *       @OA\Property(
 *         property="number",
 *         type="string",
 *         description="Number of the phone number (e.g., '5550077').",
 *         example="5550077"
 *       )
 *     ),
 *     @OA\Property(
 *       property="rooms",
 *       type="array",
 *       description="Rooms of the booking",
 *       @OA\Items(
 *         type="object",
 *         required={"given_name", "family_name"},
 *         @OA\Property(
 *           property="given_name",
 *           type="string",
 *           description="Given name of the customer (e.g., 'John').",
 *           example="John"
 *         ),
 *         @OA\Property(
 *           property="family_name",
 *           type="string",
 *           description="Family name of the customer (e.g., 'Portman').",
 *           example="Portman"
 *         ),
 *         @OA\Property(
 *           property="smoking",
 *           type="boolean",
 *           description="Smoking room (e.g., 'false').",
 *           example="false"
 *         )
 *       )
 *     ),
 *     @OA\Property(
 *       property="payments",
 *       type="array",
 *       description="Payments of the booking",
 *       @OA\Items(
 *         type="object",
 *         required={"type", "billing_contact"},
 *         @OA\Property(
 *           property="type",
 *           type="string",
 *           description="Type of the payment (e.g., 'affiliate_collect').",
 *           enum={"affiliate_collect"},
 *           example="affiliate_collect"
 *         ),
 *         @OA\Property(
 *           property="billing_contact",
 *           type="object",
 *           required={"given_name", "family_name", "address"},
 *           @OA\Property(
 *             property="given_name",
 *             type="string",
 *             description="Given name of the customer (e.g., 'John').",
 *             example="John"
 *           ),
 *           @OA\Property(
 *             property="family_name",
 *             type="string",
 *             description="Family name of the customer (e.g., 'Smith').",
 *             example="Smith"
 *           ),
 *           @OA\Property(
 *             property="address",
 *             type="object",
 *             required={"line_1", "city", "state_province_code", "postal_code", "country_code"},
 *             @OA\Property(
 *               property="line_1",
 *               type="string",
 *               description="Address line 1 (e.g., '555 1st St').",
 *               example="555 1st St"
 *             ),
 *             @OA\Property(
 *               property="city",
 *               type="string",
 *               description="City (e.g., 'Seattle').",
 *               example="Seattle"
 *             ),
 *             @OA\Property(
 *               property="state_province_code",
 *               type="string",
 *               description="State or province code (e.g., 'WA').",
 *               example="WA"
 *             ),
 *             @OA\Property(
 *               property="postal_code",
 *               type="string",
 *               description="Postal code (e.g., '98121').",
 *               example="98121"
 *             ),
 *             @OA\Property(
 *               property="country_code",
 *               type="string",
 *               description="Country code (e.g., 'US').",
 *               example="US"
 *             )
 *           )  
 *         )
 *       )    
 *     )
 *   )
 * ),          
 * @OA\Examples(
 *     example="BookingBookRequest",
 *     summary="Example Booking Book Request",
 *     value=
 * {
 *    "query":{
 *       "hold":false,
 *       "email":"john@example.com",
 *       "phone":{
 *          "country_code":"1",
 *          "area_code":"487",
 *          "number":"5550077"
 *       },
 *       "rooms":{
 *          {
 *             "given_name":"John",
 *             "family_name":"Portman",
 *             "smoking":false
 *          }
 *       },
 *       "payments":{
 *          {
 *             "type":"affiliate_collect",
 *             "billing_contact":{
 *                "given_name":"John",
 *                "family_name":"Smith",
 *                "address":{
 *                   "line_1":"555 1st St",
 *                   "city":"Seattle",
 *                   "state_province_code":"WA",
 *                   "postal_code":"98121",
 *                   "country_code":"US"
 *                }
 *             }
 *          }
 *       }
 *    }
 * }
 * )
 */

class BookingBookRequest
{
}
