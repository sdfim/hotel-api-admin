<?php

namespace Modules\API\Resources\Booking;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="BookingRetrieveItemsResponse",
 *   title="Booking Retrieve Items Response",
 *   description="Schema of Booking Retrieve Items Response",
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
 *         property="booking_items",
 *         type="array",
 *         description="Booking Items",
 *         @OA\Items(
 *           type="object",
 *           description="Booking Item",
 *           @OA\Property(
 *             property="booking_item",
 *             type="string",
 *             description="Booking Item (e.g., 'f8287abe-52be-43a2-8354-b8c4327786a4').",
 *             example="f8287abe-52be-43a2-8354-b8c4327786a4"
 *           ),
 *           @OA\Property(
 *             property="booking_item_data",
 *             type="object",
 *             description="Booking Item Data",
 *             @OA\Property(
 *               property="rate",
 *               type="string",
 *               description="Rate (e.g., '382884473').",
 *               example="382884473"
 *             ),
 *             @OA\Property(
 *               property="room_id",
 *               type="string",
 *               description="Room ID (e.g., '218129262').",
 *               example="218129262"
 *             ),
 *             @OA\Property(
 *               property="hotel_id",
 *               type="integer",
 *               description="Hotel ID (e.g., 60295986).",
 *               example=60295986
 *             ),
 *             @OA\Property(
 *               property="bed_groups",
 *               type="integer",
 *               description="Bed Groups (e.g., 37321).",
 *               example=37321
 *             ),
 *             @OA\Property(
 *               property="room",
 *               type="object",
 *               description="Room",
 *               @OA\Property(
 *                 property="giata_room_code",
 *                 type="string",
 *                 description="Giata Room Code (e.g., '').",
 *                 example=""
 *               ),
 *               @OA\Property(
 *                 property="giata_room_name",
 *                 type="string",
 *                 description="Giata Room Name (e.g., '').",
 *                 example=""
 *               ),
 *               @OA\Property(
 *                 property="supplier_room_name",
 *                 type="string",
 *                 description="Supplier Room Name (e.g., 'Superior Room, 1 King Bed, Non Smoking').",
 *                 example="Superior Room, 1 King Bed, Non Smoking"
 *               ),
 *               @OA\Property(
 *                 property="per_day_rate_breakdown",
 *                 type="string",
 *                 description="Per Day Rate Breakdown (e.g., '').",
 *                 example=""
 *               ),
 *               @OA\Property(
 *                 property="total_price",
 *                 type="number",
 *                 description="Total Price (e.g., 6757.5).",
 *                 example=6757.5
 *               ),
 *               @OA\Property(
 *                 property="total_tax",
 *                 type="number",
 *                 description="Total Tax (e.g., 917.5).",
 *                 example=917.5
 *               ),
 *               @OA\Property(
 *                 property="total_fees",
 *                 type="number",
 *                 description="Total Fees (e.g., 480).",
 *                 example=480
 *               ),
 *               @OA\Property(
 *                 property="total_net",
 *                 type="number",
 *                 description="Total Net (e.g., 5840).",
 *                 example=5840
 *               ),
 *               @OA\Property(
 *                 property="affiliate_service_charge",
 *                 type="number",
 *                 description="Affiliate Service Charge (e.g., 5197.6).",
 *                 example=5197.6
 *               ),
 *               @OA\Property(
 *                 property="booking_item",
 *                 type="string",
 *                 description="Booking Item (e.g., '7ba653c3-8f9a-46c0-83c2-b8a23bbd908b').",
 *                 example="7ba653c3-8f9a-46c0-83c2-b8a23bbd908b"
 *               )
 *             )
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
 *     example="BookingRetrieveItemsResponse",
 *     summary="Example of Booking Retrieve Items Response",
 *     value=
 * {
 * 	"success": true,
 * 	"data": {
 * 		"result": {
 * 			"booking_id": "c698abfe-9bfa-45ee-a201-dc7322e008ab",
 * 			"booking_items": {
 * 				{
 * 					"booking_item": "7ba653c3-8f9a-46c0-83c2-b8a23bbd908b",
 * 					"booking_item_data": {
 * 						"rate": "382884473",
 * 						"room_id": "218129262",
 * 						"hotel_id": 60295986,
 * 						"bed_groups": 37321
 * 					},
 * 					"room": {
 * 						"giata_room_code": "",
 * 						"giata_room_name": "",
 * 						"supplier_room_name": "Superior Room, 1 King Bed, Non Smoking",
 * 						"per_day_rate_breakdown": "",
 * 						"total_price": 6757.5,
 * 						"total_tax": 917.5,
 * 						"total_fees": 480,
 * 						"total_net": 5840,
 * 						"affiliate_service_charge": 5197.6,
 * 						"booking_item": "7ba653c3-8f9a-46c0-83c2-b8a23bbd908b"
 * 					}
 * 				},
 * 				{
 * 					"booking_item": "8d9424f7-8967-41c7-a1a4-4d80f958ef50",
 * 					"booking_item_data": {
 * 						"rate": "388777728",
 * 						"room_id": "321415108",
 * 						"hotel_id": 60295986,
 * 						"bed_groups": 37321
 * 					},
 * 					"room": {
 * 						"giata_room_code": "",
 * 						"giata_room_name": "",
 * 						"supplier_room_name": "Superior Room, 1 King Bed, Non Smoking (Mobility\/Hearing Accessible, Tub)",
 * 						"per_day_rate_breakdown": "",
 * 						"total_price": 7179.7,
 * 						"total_tax": 971.7,
 * 						"total_fees": 480,
 * 						"total_net": 6208,
 * 						"affiliate_service_charge": 5525.12,
 * 						"booking_item": "8d9424f7-8967-41c7-a1a4-4d80f958ef50"
 * 					}
 * 				}
 * 			}
 * 		}
 * 	},
 * 	"message": "success"
 * }
 * )
 */

class BookingRetrieveItemsResponse
{
}
