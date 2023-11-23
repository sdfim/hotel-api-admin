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
 *     enum={"Deposit", "Full Payment"},
 *     example="Deposit"
 *   ),
 *   @OA\Property(
 *     property="booking_contact",
 *     type="object",
 *     required={"first_name", "last_name", "email", "phone", "address"},
 *     @OA\Property(
 *       property="first_name",
 *       type="string",
 *       example="John"
 *     ),
 *     @OA\Property(
 *       property="last_name",
 *       type="string",
 *       example="Smith"
 *     ),
 *     @OA\Property(
 *       property="email",
 *       type="string",
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
 *         example="1"
 *       ),
 *       @OA\Property(
 *         property="area_code",
 *         type="string",
 *         example="487"
 *       ),
 *       @OA\Property(
 *         property="number",
 *         type="string",
 *         example="5550077"
 *       )
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
 *   ),
 *   @OA\Property(
 *     property="credit_card",
 *     type="object",
 *     required={"name_card", "number", "card_type", "expiry_date", "cvv", "billing_address"},
 *     @OA\Property(
 *       property="name_card",
 *       type="string",
 *       example="Visa"
 *     ),
 *     @OA\Property(
 *       property="number",
 *       type="integer",
 *       example=4001919257537193
 *     ),
 *     @OA\Property(
 *       property="card_type",
 *       type="string",
 *       enum={"VISA", "MSC", "AMEX", "DIS"},
 *       example="VISA"
 *     ),
 *     @OA\Property(
 *       property="expiry_date",
 *       type="string",
 *       example="09/2026",
 *       format="m/Y"
 *     ),
 *     @OA\Property(
 *       property="cvv",
 *       type="integer",
 *       example=123
 *     ),
 *     @OA\Property(
 *       property="billing_address",
 *       type="string",
 *       example=""
 *     )
 *   )
 * ),       
 * @OA\Examples(
 *     example="BookingBookRequest",
 *     summary="Example Booking Book Request",
 *     value=
 * 		{
 *         "amount_pay":"Deposit",
 *         "booking_contact":{
 *            "first_name":"John",
 *            "last_name":"Smith",
 *            "email":"john@example.com",
 *            "phone":{
 *              "country_code":"1",
 *              "area_code":"487",
 *              "number":"5550077"
 *            },
 *            "address":{
 *               "line_1":"555 1st St",
 *               "city":"Seattle",
 *               "state_province_code":"WA",
 *               "postal_code":"98121",
 *               "country_code":"US"
 *            }
 *         },
 *         "credit_card":{
 *             "name_card": "Visa",
 *             "number": 4001919257537193,
 *             "card_type": "VISA",
 *             "expiry_date": "09/2026",
 *             "cvv":123,
 *             "billing_address": ""
 *         }
 *      }
 * )
 */

class BookingBookRequest
{
}
