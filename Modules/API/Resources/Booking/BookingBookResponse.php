<?php

namespace Modules\API\Resources\Booking;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="BookingBookResponse",
 *   title="Booking Book Response",
 *   description="Schema of Booking Book Response",
 *   type="object",
 *   required={"success", "data", "message"},
 *   @OA\Property(
 *     property="success",
 *     type="boolean",
 *     description="Success (e.g., 'true').",
 *     example="true"
 *   ),
 *   @OA\Property(
 *     property="data",
 *     type="object",
 *     description="Data",
 *     @OA\Property(
 *       property="result",
 *       type="object",
 *       description="Result",
 *       @OA\Property(
 *         property="booking_id",
 *         type="string",
 *         description="Booking ID (e.g., 'c698abfe-9bfa-45ee-a201-dc7322e008ab').",
 *         example="c698abfe-9bfa-45ee-a201-dc7322e008ab"
 *       ),
 *       @OA\Property(
 *         property="search_id",
 *         type="string",
 *         description="Search ID (e.g., 'd2ff0669-8d0e-4709-826d-ccdcef01d4a2').",
 *         example="d2ff0669-8d0e-4709-826d-ccdcef01d4a2"
 *       ),
 *       @OA\Property(
 *         property="booking_item",
 *         type="string",
 *         description="Booking Item",
 *         example="f8287abe-52be-43a2-8354-b8c4327786a4"
 *       ),
 *       @OA\Property(
 *         property="links",
 *         type="object",
 *         description="Links",
 *         @OA\Property(
 *           property="remove",
 *           type="object",
 *           description="Remove",
 *           @OA\Property(
 *             property="method",
 *             type="string",
 *             description="Method (e.g., 'DELETE').",
 *             example="DELETE"
 *           ),
 *           @OA\Property(
 *             property="href",
 *             type="string",
 *             description="Href (e.g., '/api/booking/cancel-booking?booking_id=c698abfe-9bfa-45ee-a201-dc7322e008ab&booking_item=f8287abe-52be-43a2-8354-b8c4327786a4').",
 *             example="/api/booking/cancel-booking?booking_id=c698abfe-9bfa-45ee-a201-dc7322e008ab&booking_item=f8287abe-52be-43a2-8354-b8c4327786a4"
 *           )
 *         ),
 *         @OA\Property(
 *           property="change",
 *           type="object",
 *           description="Change",
 *           @OA\Property(
 *             property="method",
 *             type="string",
 *             description="Method (e.g., 'PUT').",
 *             example="PUT"
 *           ),
 *           @OA\Property(
 *             property="href",
 *             type="string",
 *             description="Href (e.g., '/api/booking/change-booking?booking_id=c698abfe-9bfa-45ee-a201-dc7322e008ab&booking_item=f8287abe-52be-43a2-8354-b8c4327786a4').",
 *             example="/api/booking/change-booking?booking_id=c698abfe-9bfa-45ee-a201-dc7322e008ab&booking_item=f8287abe-52be-43a2-8354-b8c4327786a4"
 *           )
 *         ),
 *         @OA\Property(
 *           property="retrieve",
 *           type="object",
 *           description="Retrieve",
 *           @OA\Property(
 *             property="method",
 *             type="string",
 *             description="Method (e.g., 'GET').",
 *             example="GET"
 *           ),
 *           @OA\Property(
 *             property="href",
 *             type="string",
 *             description="Href (e.g., '/api/booking/retrieve-booking?booking_id=c698abfe-9bfa-45ee-a201-dc7322e008ab').",
 *             example="/api/booking/retrieve-booking?booking_id=c698abfe-9bfa-45ee-a201-dc7322e008ab"
 *           )
 *         )
 *       )
 *     )
 *   ),
 *   @OA\Property(
 *     property="message",
 *     type="string",
 *     description="Message (e.g., 'success').",
 *     example="success"
 *   )
 * ),
 * @OA\Examples(
 *     example="BookingBookResponse",
 *     summary="Example of Booking Book Response",
 *     value=
 * {
 * 	"success": true,
 * 	"data": {
 * 		{
 * 			"booking_id": "5abcab52-00b0-423c-aafe-2fed6f6d1f4e",
 * 			"search_id": "d2ff0669-8d0e-4709-826d-ccdcef01d4a2",
 * 			"booking_item": "bd5e6c4e-6e5c-49cf-8ddf-c52abe49b764",
 * 			"links": {
 * 				"remove": {
 * 					"method": "DELETE",
 * 					"href": "\/api\/booking\/cancel-booking?booking_id=5abcab52-00b0-423c-aafe-2fed6f6d1f4e&booking_item=bd5e6c4e-6e5c-49cf-8ddf-c52abe49b764"
 * 				},
 * 				"change": {
 * 					"method": "PUT",
 * 					"href": "\/api\/booking\/change-booking?booking_id=5abcab52-00b0-423c-aafe-2fed6f6d1f4e&booking_item=bd5e6c4e-6e5c-49cf-8ddf-c52abe49b764"
 * 				},
 * 				"retrieve": {
 * 					"method": "GET",
 * 					"href": "\/api\/booking\/retrieve-booking?booking_id=5abcab52-00b0-423c-aafe-2fed6f6d1f4e"
 * 				}
 * 			}
 * 		},
 * 		{
 * 			"booking_id": "5abcab52-00b0-423c-aafe-2fed6f6d1f4e",
 * 			"search_id": "d2ff0669-8d0e-4709-826d-ccdcef01d4a2",
 * 			"booking_item": "bed7af51-836a-4d7e-8f82-8efe8b1825d4",
 * 			"links": {
 * 				"remove": {
 * 					"method": "DELETE",
 * 					"href": "\/api\/booking\/cancel-booking?booking_id=5abcab52-00b0-423c-aafe-2fed6f6d1f4e&booking_item=bed7af51-836a-4d7e-8f82-8efe8b1825d4"
 * 				},
 * 				"change": {
 * 					"method": "PUT",
 * 					"href": "\/api\/booking\/change-booking?booking_id=5abcab52-00b0-423c-aafe-2fed6f6d1f4e&booking_item=bed7af51-836a-4d7e-8f82-8efe8b1825d4"
 * 				},
 * 				"retrieve": {
 * 					"method": "GET",
 * 					"href": "\/api\/booking\/retrieve-booking?booking_id=5abcab52-00b0-423c-aafe-2fed6f6d1f4e"
 * 				}
 * 			}
 * 		}
 * 	},
 * 	"message": "success"
 * }
 * )
 */

class BookingBookResponse
{
}
