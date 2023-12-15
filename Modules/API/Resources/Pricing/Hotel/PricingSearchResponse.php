<?php

namespace Modules\API\Resources\Pricing\Hotel;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="PricingSearchResponse",
 *   title="Pricing Search Response",
 *   description="Pricing Search Response",
 *   type="object",
 *   required={"success", "data"},
 *   @OA\Property(
 *     property="data",
 *     type="object",
 *     required={"count", "query", "results", "search_id"},
 *     @OA\Property(
 *       property="count",
 *       description="Number of results returned.",
 *       type="integer",
 *       example="6"
 *     ),
 *     @OA\Property(
 *       property="query",
 *       description="Query used to generate the results.",
 *       type="object",
 *       @OA\Property(
 *         property="checkin",
 *         description="Checkin date.",
 *         type="string",
 *         format="date",
 *         example="2023-11-11"
 *       ),
 *       @OA\Property(
 *         property="checkout",
 *         description="Checkout date.",
 *         type="string",
 *         format="date",
 *         example="2023-11-20"
 *       ),
 *       @OA\Property(
 *         property="destination",
 *         description="Destination ID.",
 *         type="integer",
 *         example="961"
 *       ),
 *       @OA\Property(
 *         property="rating",
 *         description="Rating of the hotel.",
 *         type="number",
 *         enum={1.0, 1.5, 2.0, 2.5, 3.0, 3.5, 4.0, 4.5, 5.0, 5.5},
 *         example="4.5"
 *       ),
 *       @OA\Property(
 *         property="occupancy",
 *         description="Occupancy of the hotel",
 *         type="array",
 *         @OA\Items(
 *          type="object",
 *          required={"adults"},
 *          @OA\Property(
 *            property="adults",
 *            description="Number of adults.",
 *            type="integer",
 *            example="2"
 *          ),
 *          @OA\Property(
 *            property="children",
 *            description="Number of children.",
 *            type="integer",
 *            example="2"
 *          )
 *        )
 *      )
 *    ),
 *    @OA\Property(
 *      type="results",
 *      description="Results of the search.",
 *      type="array",
 *      @OA\Items(
 *        type="object",
 *        required={"giata_hotel_id", "supplier", "supplier_hotel_id", "destination", "lowest_priced_room_group", "non_refundable_rates", "refundable_rates", "room_groups", "weight"},
 *        @OA\Property(
 *          property="giata_hotel_id",
 *          description="Giata hotel ID.",
 *          type="integer",
 *          example="61596742"
 *        ),
 *        @OA\Property(
 *          property="supplier",
 *          description="Supplier of the hotel.",
 *          type="string",
 *          example="Expedia"
 *        ),
 *        @OA\Property(
 *          property="supplier_hotel_id",
 *          description="Supplier hotel ID.",
 *          type="integer",
 *          example="4818"
 *        ),
 *        @OA\Property(
 *          property="destination",
 *          description="Destination of the hotel.",
 *          type="string",
 *          example="New York, New York, USA"
 *        ),
 *        @OA\Property(
 *          property="meal_plans_available",
 *          description="Meal plans available.",
 *          type="string",
 *          example=""
 *        ),
 *        @OA\Property(
 *          property="lowest_priced_room_group",
 *          description="Lowest priced room group.",
 *          type="string",
 *          example="31184.74"
 *        ),
 *        @OA\Property(
 *          property="pay_at_hotel_available",
 *          description="Pay at hotel available.",
 *          type="string",
 *          example=""
 *        ),
 *        @OA\Property(
 *          property="pay_now_available",
 *          description="Pay now available.",
 *          type="string",
 *          example=""
 *        ),
 *        @OA\Property(
 *          property="non_refundable_rates",
 *          description="Non refundable rates.",
 *          type="string",
 *          example="385802351"
 *        ),
 *        @OA\Property(
 *          property="refundable_rates",
 *          description="Refundable rates.",
 *          type="string",
 *          example=""
 *        ),
 *        @OA\Property(
 *          property="room_groups",
 *          description="Room groups.",
 *          type="array",
 *          @OA\Items(
 *            type="object",
 *            required={"total_price", "total_tax", "total_fees", "total_net", "affiliate_service_charge", "currency", "pay_now", "pay_at_hotel", "non_refundable", "meal_plan", "rate_id", "rate_description", "cancellation_policies", "opaque", "rooms"},
 *            @OA\Property(
 *              property="total_price",
 *              description="Total price.",
 *              type="number",
 *              example="31184.74"
 *            ),
 *            @OA\Property(
 *              property="total_tax",
 *              description="Total tax.",
 *              type="number",
 *              example="4094.74"
 *            ),
 *            @OA\Property(
 *              property="total_fees",
 *              description="Total fees.",
 *              type="number",
 *              example="0"
 *            ),
 *            @OA\Property(
 *              property="total_net",
 *              description="Total net.",
 *              type="number",
 *              example="27090"
 *            ),
 *            @OA\Property(
 *              property="affiliate_service_charge",
 *              description="Affiliate service charge.",
 *              type="number",
 *              example="44"
 *            ),
 *            @OA\Property(
 *              property="currency",
 *              description="Currency.",
 *              type="string",
 *              example="USD"
 *            ),
 *            @OA\Property(
 *              property="pay_now",
 *              description="Pay now.",
 *              type="boolean",
 *              example="false"
 *            ),
 *            @OA\Property(
 *              property="pay_at_hotel",
 *              description="Pay at hotel.",
 *              type="boolean",
 *              example="false"
 *            ),
 *            @OA\Property(
 *              property="non_refundable",
 *              description="Non refundable.",
 *              type="boolean",
 *              example="true"
 *            ),
 *            @OA\Property(
 *              property="meal_plan",
 *              description="Meal plan.",
 *              type="string",
 *              example=""
 *             ),
 *             @OA\Property(
 *               property="rate_id",
 *               description="Rate ID.",
 *               type="integer",
 *               example="385802351"
 *             ),
 *             @OA\Property(
 *               property="rate_description",
 *               description="Rate description.",
 *               type="string",
 *               example=""
 *             ),
 *             @OA\Property(
 *               property="cancellation_policies",
 *               description="Cancellation policies.",
 *               type="array",
 *               @OA\Items(
 *                 type="object",
 *                 required={"start", "end", "percent", "currency"},
 *                 @OA\Property(
 *                   property="start",
 *                   description="Start date.",
 *                   type="string",
 *                   format="date-time",
 *                   example="2023-11-03T07:26:55.615-05:00"
 *                 ),
 *                 @OA\Property(
 *                   property="end",
 *                   description="End date.",
 *                   type="string",
 *                   format="date-time",
 *                   example="2023-11-11T14:00:00.000-05:00"
 *                 ),
 *                 @OA\Property(
 *                   property="percent",
 *                   description="Percent.",
 *                   type="string",
 *                   example="100%"
 *                 ),
 *                 @OA\Property(
 *                   property="currency",
 *                   description="Currency.",
 *                   type="string",
 *                   example="USD"
 *                 )
 *               )
 *             ),
 *             @OA\Property(
 *               property="opaque",
 *               description="Opaque.", *             
 *               type="boolean",
 *               example="false"
 *             ),
 *             @OA\Property(
 *               property="rooms",
 *               description="Rooms.",
 *               type="array",
 *               @OA\Items(
 *                 type="object",
 *                 required={"giata_room_code", "giata_room_name", "supplier_room_name", "per_day_rate_breakdown", "total_price", "total_tax", "total_fees", "total_net", "affiliate_service_charge", "booking_item"},
 *                 @OA\Property(
 *                   property="giata_room_code",
 *                   description="Giata room code.",
 *                   type="string",
 *                   example=""
 *                 ),
 *                 @OA\Property(
 *                   property="giata_room_name",
 *                   description="Giata room name.",
 *                   type="string",
 *                   example=""
 *                 ),
 *                 @OA\Property(
 *                   property="supplier_room_name",
 *                   description="Supplier room name.",
 *                   type="string",
 *                   example="Family Suite, Multiple Beds (Palace, King and Two Doubles)"
 *                 ),
 *                 @OA\Property(
 *                   property="per_day_rate_breakdown",
 *                   description="Per day rate breakdown.",
 *                   type="string",
 *                   example=""
 *                 ),
 *                 @OA\Property(
 *                   property="total_price",
 *                   description="Total price.",
 *                   type="number",
 *                   example="31184.74"
 *                 ),
 *                 @OA\Property(
 *                   property="total_tax",
 *                   description="Total tax.",
 *                   type="number",
 *                   example="4094.74"
 *                 ),
 *                 @OA\Property(
 *                   property="total_fees",
 *                   description="Total fees.",
 *                   type="number",
 *                   example="0"
 *                 ),
 *                 @OA\Property(
 *                   property="total_net",
 *                   description="Total net.",
 *                   type="number",
 *                   example="27090"
 *                 ),
 *                 @OA\Property(
 *                   property="affiliate_service_charge",
 *                   description="Affiliate service charge.",
 *                   type="number",
 *                   example="44"
 *                 ),
 *                 @OA\Property(
 *                   property="booking_item",
 *                   description="Booking item.",
 *                   type="string",
 *                   example="4154e990-6d0b-4852-bcd3-52f1e5a44f14"
 *                 )
 *               )
 *             )
 *           )
 *         ),
 *         @OA\Property(
 *           property="weight",
 *           description="Weight.",
 *           type="integer",
 *           example="4502"
 *         )
 *       )
 *     ),
 *     @OA\Property(
 *       property="search_id",
 *       description="Search ID.",
 *       type="string",
 *       example="5f9f1b4c-5b7c-4b0e-8b0a-5e9e1b4c5b7c"
 *     )
 *   )
 * )
 * @OA\Examples(
 *     example="PricingSearchResponseNewYork",
 *     summary="An Example Pricing Search Response New York",
 *     value=
 * {
 *     "success": true,
 *     "data": {
 *         "count": 6,
 *         "query": {
 *             "checkin": "2023-11-11",
 *             "checkout": "2023-11-20",
 *             "destination": 961,
 *             "rating": 4.5,
 *             "occupancy": {
 *                 {
 *                     "adults": 4,
 *                     "children": 1
 *                 },
 *                 {
 *                     "adults": 3
 *                 }
 *             }
 *         },
 *         "results": {
 *             "Expedia": {
 *                 {
 *                     "giata_hotel_id": 61596742,
 *                     "supplier": "Expedia",
 *                     "supplier_hotel_id": 4818,
 *                     "destination": "New York, New York, USA",
 *                     "meal_plans_available": "",
 *                     "lowest_priced_room_group": "31184.74",
 *                     "pay_at_hotel_available": "",
 *                     "pay_now_available": "",
 *                     "non_refundable_rates": "385802351",
 *                     "refundable_rates": "",
 *                     "room_groups": {
 *                         {
 *                             "total_price": 31184.74,
 *                             "total_tax": 4094.74,
 *                             "total_fees": 0,
 *                             "total_net": 27090,
 *                             "affiliate_service_charge": 44,
 *                             "currency": "USD",
 *                             "pay_now": false,
 *                             "pay_at_hotel": false,
 *                             "non_refundable": true,
 *                             "meal_plan": "",
 *                             "rate_id": 385802351,
 *                             "rate_description": "",
 *                             "cancellation_policies": {
 *                                 {
 *                                     "start": "2023-11-03T07:26:55.615-05:00",
 *                                     "end": "2023-11-11T14:00:00.000-05:00",
 *                                     "percent": "100%",
 *                                     "currency": "USD"
 *                                 }
 *                             },
 *                             "opaque": false,
 *                             "rooms": {
 *                                 {
 *                                     "giata_room_code": "",
 *                                     "giata_room_name": "",
 *                                     "supplier_room_name": "Family Suite, Multiple Beds (Palace, King and Two Doubles)",
 *                                     "per_day_rate_breakdown": "",
 *                                     "total_price": 31184.74,
 *                                     "total_tax": 4094.74,
 *                                     "total_fees": 0,
 *                                     "total_net": 27090,
 *                                     "affiliate_service_charge": 44,
 *                                     "booking_item": "4154e990-6d0b-4852-bcd3-52f1e5a44f14"
 *                                 }
 *                             }
 *                         }
 *                     },
 *                     "weight": 4502
 *                 },
 *                 {
 *                     "giata_hotel_id": 80621142,
 *                     "supplier": "Expedia",
 *                     "supplier_hotel_id": 6365,
 *                     "destination": "New York, New York, USA",
 *                     "meal_plans_available": "",
 *                     "lowest_priced_room_group": "",
 *                     "pay_at_hotel_available": "",
 *                     "pay_now_available": "",
 *                     "non_refundable_rates": "390898979",
 *                     "refundable_rates": "",
 *                     "room_groups": {
 *                         {
 *                             "total_price": 156044.4,
 *                             "total_tax": 20144.2,
 *                             "total_fees": 900,
 *                             "total_net": 135900.2,
 *                             "affiliate_service_charge": 2718,
 *                             "currency": "USD",
 *                             "pay_now": false,
 *                             "pay_at_hotel": false,
 *                             "non_refundable": true,
 *                             "meal_plan": "",
 *                             "rate_id": 390898979,
 *                             "rate_description": "",
 *                             "cancellation_policies": {
 *                                 {
 *                                     "start": "2023-11-03T07:26:55.615-05:00",
 *                                     "end": "2023-11-11T16:00:00.000-05:00",
 *                                     "percent": "100%",
 *                                     "currency": "USD"
 *                                 }
 *                             },
 *                             "opaque": false,
 *                             "rooms": {
 *                                 {
 *                                     "giata_room_code": "",
 *                                     "giata_room_name": "",
 *                                     "supplier_room_name": "Grand Suite, 1 Double Bed, Corner",
 *                                     "per_day_rate_breakdown": "",
 *                                     "total_price": 156044.4,
 *                                     "total_tax": 20144.2,
 *                                     "total_fees": 900,
 *                                     "total_net": 135900.2,
 *                                     "affiliate_service_charge": 2718,
 *                                     "booking_item": "cd362e27-62ac-4231-897d-06b29347e78a"
 *                                 }
 *                             }
 *                         }
 *                     },
 *                     "weight": 4547
 *                 },
 *                 {
 *                     "giata_hotel_id": 68487531,
 *                     "supplier": "Expedia",
 *                     "supplier_hotel_id": 15838,
 *                     "destination": "New York, New York, USA",
 *                     "meal_plans_available": "",
 *                     "lowest_priced_room_group": "24496.98",
 *                     "pay_at_hotel_available": "",
 *                     "pay_now_available": "",
 *                     "non_refundable_rates": "",
 *                     "refundable_rates": "390129408,200456380",
 *                     "room_groups": {
 *                         {
 *                             "total_price": 24496.98,
 *                             "total_tax": 3203.68,
 *                             "total_fees": 619.74,
 *                             "total_net": 21293.3,
 *                             "affiliate_service_charge": 21293.3,
 *                             "currency": "USD",
 *                             "pay_now": false,
 *                             "pay_at_hotel": false,
 *                             "non_refundable": false,
 *                             "meal_plan": "",
 *                             "rate_id": 390129408,
 *                             "rate_description": "",
 *                             "cancellation_policies": {
 *                                 {
 *                                     "start": "2023-11-08T23:59:00.000-05:00",
 *                                     "end": "2023-11-11T23:59:00.000-05:00",
 *                                     "nights": "1",
 *                                     "currency": "USD"
 *                                 }
 *                             },
 *                             "opaque": false,
 *                             "rooms": {
 *                                 {
 *                                     "giata_room_code": "",
 *                                     "giata_room_name": "",
 *                                     "supplier_room_name": "Family Room, 1 Double Bed, Non Smoking, Corner",
 *                                     "per_day_rate_breakdown": "",
 *                                     "total_price": 24496.98,
 *                                     "total_tax": 3203.68,
 *                                     "total_fees": 619.74,
 *                                     "total_net": 21293.3,
 *                                     "affiliate_service_charge": 21293.3,
 *                                     "booking_item": "dcb9a74e-29db-4b15-aff9-4468eb8c90c1"
 *                                 }
 *                             }
 *                         },
 *                         {
 *                             "total_price": 26126.6,
 *                             "total_tax": 3444.6,
 *                             "total_fees": 619.74,
 *                             "total_net": 22682,
 *                             "affiliate_service_charge": 22682,
 *                             "currency": "USD",
 *                             "pay_now": false,
 *                             "pay_at_hotel": false,
 *                             "non_refundable": false,
 *                             "meal_plan": "",
 *                             "rate_id": 200456380,
 *                             "rate_description": "",
 *                             "cancellation_policies": {
 *                                 {
 *                                     "start": "2023-11-08T23:59:00.000-05:00",
 *                                     "end": "2023-11-11T23:59:00.000-05:00",
 *                                     "nights": "1",
 *                                     "currency": "USD"
 *                                 }
 *                             },
 *                             "opaque": false,
 *                             "rooms": {
 *                                 {
 *                                     "giata_room_code": "",
 *                                     "giata_room_name": "",
 *                                     "supplier_room_name": "Suite, 1 Bedroom, Non Smoking",
 *                                     "per_day_rate_breakdown": "",
 *                                     "total_price": 26126.6,
 *                                     "total_tax": 3444.6,
 *                                     "total_fees": 619.74,
 *                                     "total_net": 22682,
 *                                     "affiliate_service_charge": 22682,
 *                                     "booking_item": "e4b0ec3a-be3c-423c-ad6d-3165bd368683"
 *                                 }
 *                             }
 *                         }
 *                     },
 *                     "weight": 4295
 *                 },
 *                 {
 *                     "giata_hotel_id": 25749746,
 *                     "supplier": "Expedia",
 *                     "supplier_hotel_id": 40338,
 *                     "destination": "New York, New York, USA",
 *                     "meal_plans_available": "",
 *                     "lowest_priced_room_group": "71904.92",
 *                     "pay_at_hotel_available": "",
 *                     "pay_now_available": "",
 *                     "non_refundable_rates": "",
 *                     "refundable_rates": "204800713",
 *                     "room_groups": {
 *                         {
 *                             "total_price": 71904.92,
 *                             "total_tax": 9328.92,
 *                             "total_fees": 0,
 *                             "total_net": 62576,
 *                             "affiliate_service_charge": 42551.68,
 *                             "currency": "USD",
 *                             "pay_now": false,
 *                             "pay_at_hotel": false,
 *                             "non_refundable": false,
 *                             "meal_plan": "",
 *                             "rate_id": 204800713,
 *                             "rate_description": "",
 *                             "cancellation_policies": {
 *                                 {
 *                                     "start": "2023-11-10T23:59:00.000-05:00",
 *                                     "end": "2023-11-11T23:59:00.000-05:00",
 *                                     "nights": "1",
 *                                     "currency": "USD"
 *                                 }
 *                             },
 *                             "opaque": false,
 *                             "rooms": {
 *                                 {
 *                                     "giata_room_code": "",
 *                                     "giata_room_name": "",
 *                                     "supplier_room_name": "Suite, 2 Bedrooms, Park View",
 *                                     "per_day_rate_breakdown": "",
 *                                     "total_price": 71904.92,
 *                                     "total_tax": 9328.92,
 *                                     "total_fees": 0,
 *                                     "total_net": 62576,
 *                                     "affiliate_service_charge": 42551.68,
 *                                     "booking_item": "c1d6cd10-311a-4d84-8be7-faab6358eca1"
 *                                 }
 *                             }
 *                         }
 *                     },
 *                     "weight": 4450
 *                 },
 *                 {
 *                     "giata_hotel_id": 48428306,
 *                     "supplier": "Expedia",
 *                     "supplier_hotel_id": 40553,
 *                     "destination": "New York, New York, USA",
 *                     "meal_plans_available": "",
 *                     "lowest_priced_room_group": "",
 *                     "pay_at_hotel_available": "",
 *                     "pay_now_available": "",
 *                     "non_refundable_rates": "",
 *                     "refundable_rates": "386134984",
 *                     "room_groups": {
 *                         {
 *                             "total_price": 121128.18,
 *                             "total_tax": 15656.18,
 *                             "total_fees": 0,
 *                             "total_net": 105472,
 *                             "affiliate_service_charge": 95979.52,
 *                             "currency": "USD",
 *                             "pay_now": false,
 *                             "pay_at_hotel": false,
 *                             "non_refundable": false,
 *                             "meal_plan": "",
 *                             "rate_id": 386134984,
 *                             "rate_description": "",
 *                             "cancellation_policies": {
 *                                 {
 *                                     "start": "2023-11-10T15:00:00.000-05:00",
 *                                     "end": "2023-11-11T15:00:00.000-05:00",
 *                                     "nights": "1",
 *                                     "currency": "USD"
 *                                 }
 *                             },
 *                             "opaque": false,
 *                             "rooms": {
 *                                 {
 *                                     "giata_room_code": "",
 *                                     "giata_room_name": "",
 *                                     "supplier_room_name": "Premier Suite, 2 Bedrooms",
 *                                     "per_day_rate_breakdown": "",
 *                                     "total_price": 121128.18,
 *                                     "total_tax": 15656.18,
 *                                     "total_fees": 0,
 *                                     "total_net": 105472,
 *                                     "affiliate_service_charge": 95979.52,
 *                                     "booking_item": "6f33bcc4-6d98-4659-af02-0dd061b97ab2"
 *                                 }
 *                             }
 *                         }
 *                     },
 *                     "weight": 8761
 *                 },
 *                 {
 *                     "giata_hotel_id": 62076226,
 *                     "supplier": "Expedia",
 *                     "supplier_hotel_id": 21783007,
 *                     "destination": "New York, New York, USA",
 *                     "meal_plans_available": "",
 *                     "lowest_priced_room_group": "27026.24",
 *                     "pay_at_hotel_available": "",
 *                     "pay_now_available": "",
 *                     "non_refundable_rates": "",
 *                     "refundable_rates": "212242780,212242714",
 *                     "room_groups": {
 *                         {
 *                             "total_price": 30511.76,
 *                             "total_tax": 4008.26,
 *                             "total_fees": 929.34,
 *                             "total_net": 26503.5,
 *                             "affiliate_service_charge": 18287.42,
 *                             "currency": "USD",
 *                             "pay_now": false,
 *                             "pay_at_hotel": false,
 *                             "non_refundable": false,
 *                             "meal_plan": "",
 *                             "rate_id": 212242780,
 *                             "rate_description": "",
 *                             "cancellation_policies": {
 *                                 {
 *                                     "start": "2023-11-09T15:00:00.000-05:00",
 *                                     "end": "2023-11-11T15:00:00.000-05:00",
 *                                     "nights": "1",
 *                                     "currency": "USD"
 *                                 }
 *                             },
 *                             "opaque": false,
 *                             "rooms": {
 *                                 {
 *                                     "giata_room_code": "",
 *                                     "giata_room_name": "",
 *                                     "supplier_room_name": "Suite, 2 Bedrooms, River View (SoHi)",
 *                                     "per_day_rate_breakdown": "",
 *                                     "total_price": 30511.76,
 *                                     "total_tax": 4008.26,
 *                                     "total_fees": 929.34,
 *                                     "total_net": 26503.5,
 *                                     "affiliate_service_charge": 18287.42,
 *                                     "booking_item": "3732b4c8-0b11-4750-bf1c-18f3727927c4"
 *                                 }
 *                             }
 *                         },
 *                         {
 *                             "total_price": 27026.24,
 *                             "total_tax": 3560.24,
 *                             "total_fees": 929.34,
 *                             "total_net": 23466,
 *                             "affiliate_service_charge": 16191.54,
 *                             "currency": "USD",
 *                             "pay_now": false,
 *                             "pay_at_hotel": false,
 *                             "non_refundable": false,
 *                             "meal_plan": "",
 *                             "rate_id": 212242714,
 *                             "rate_description": "",
 *                             "cancellation_policies": {
 *                                 {
 *                                     "start": "2023-11-09T15:00:00.000-05:00",
 *                                     "end": "2023-11-11T15:00:00.000-05:00",
 *                                     "nights": "1",
 *                                     "currency": "USD"
 *                                 }
 *                             },
 *                             "opaque": false,
 *                             "rooms": {
 *                                 {
 *                                     "giata_room_code": "",
 *                                     "giata_room_name": "",
 *                                     "supplier_room_name": "Suite, 2 Bedrooms, River View (Skyline)(1 Bedroom Suite & King Room)",
 *                                     "per_day_rate_breakdown": "",
 *                                     "total_price": 27026.24,
 *                                     "total_tax": 3560.24,
 *                                     "total_fees": 929.34,
 *                                     "total_net": 23466,
 *                                     "affiliate_service_charge": 16191.54,
 *                                     "booking_item": "77471766-4455-4294-a433-7188e0993a69"
 *                                 }
 *                             }
 *                         }
 *                     },
 *                     "weight": 9329
 *                 }
 *             }
 *         },
 *         "search_id": "dbbbbd71-ab53-42c6-a625-64b8d17a12c4"
 *     },
 *     "message": "success"
 * }
 * ),
 * @OA\Examples(
 *     example="PricingSearchResponseLondon",
 *     summary="An Example Pricing Search Response London",
 *     value=
 * {
 *     "success": true,
 *     "data": {
 *         "count": 94,
 *         "query": {
 *             "checkin": "2023-11-11",
 *             "checkout": "2023-11-20",
 *             "destination": 302,
 *             "rating": 4,
 *             "occupancy": {
 *                 {
 *                     "adults": 2,
 *                     "children": 2
 *                 },
 *                 {
 *                     "adults": 3
 *                 }
 *             }
 *         },
 *         "results": {
 *             "Expedia": {
 *                 {
 *                     "giata_hotel_id": 19778080,
 *                     "supplier": "Expedia",
 *                     "supplier_hotel_id": 74,
 *                     "destination": "London, London & surrounding area, United Kingdom",
 *                     "meal_plans_available": "",
 *                     "lowest_priced_room_group": "7415.74",
 *                     "pay_at_hotel_available": "",
 *                     "pay_now_available": "",
 *                     "non_refundable_rates": "386156722",
 *                     "refundable_rates": "380255106,380213299",
 *                     "room_groups": {
 *                         {
 *                             "total_price": 7415.74,
 *                             "total_tax": 1235.92,
 *                             "total_fees": 2089.26,
 *                             "total_net": 4090.56,
 *                             "affiliate_service_charge": 0,
 *                             "currency": "USD",
 *                             "pay_now": false,
 *                             "pay_at_hotel": false,
 *                             "non_refundable": true,
 *                             "meal_plan": "",
 *                             "rate_id": 386156722,
 *                             "rate_description": "",
 *                             "cancellation_policies": {
 *                                 {
 *                                     "start": "2023-11-03T12:59:00.164+00:00",
 *                                     "end": "2023-11-11T18:00:00.000+00:00",
 *                                     "percent": "100%",
 *                                     "currency": "USD"
 *                                 }
 *                             },
 *                             "opaque": false,
 *                             "rooms": {
 *                                 {
 *                                     "giata_room_code": "",
 *                                     "giata_room_name": "",
 *                                     "supplier_room_name": "Premium Room, 2 Double Beds",
 *                                     "per_day_rate_breakdown": "",
 *                                     "total_price": 7415.74,
 *                                     "total_tax": 1235.92,
 *                                     "total_fees": 2089.26,
 *                                     "total_net": 4090.56,
 *                                     "affiliate_service_charge": 0,
 *                                     "booking_item": "d47dd61b-fee2-4406-91b4-f5087382bed0"
 *                                 },
 *                                 {
 *                                     "giata_room_code": "",
 *                                     "giata_room_name": "",
 *                                     "supplier_room_name": "Premium Room, 2 Double Beds",
 *                                     "per_day_rate_breakdown": "",
 *                                     "total_price": 8885.69,
 *                                     "total_tax": 1480.93,
 *                                     "total_fees": 2089.26,
 *                                     "total_net": 5315.5,
 *                                     "affiliate_service_charge": 0,
 *                                     "booking_item": "dfc62bc3-5e33-4f5f-8df8-407a94f517ad"
 *                                 },
 *                                 {
 *                                     "giata_room_code": "",
 *                                     "giata_room_name": "",
 *                                     "supplier_room_name": "Premium Room, 2 Double Beds",
 *                                     "per_day_rate_breakdown": "",
 *                                     "total_price": 8432.54,
 *                                     "total_tax": 1405.4,
 *                                     "total_fees": 2089.26,
 *                                     "total_net": 4937.88,
 *                                     "affiliate_service_charge": 0,
 *                                     "booking_item": "91cbc3de-7c26-475d-b9e4-4526f4efc294"
 *                                 }
 *                             }
 *                         }
 *                     },
 *                     "weight": 0
 *                 },
 *                 {
 *                     "giata_hotel_id": 30506044,
 *                     "supplier": "Expedia",
 *                     "supplier_hotel_id": 608,
 *                     "destination": "London, London & surrounding area, United Kingdom",
 *                     "meal_plans_available": "",
 *                     "lowest_priced_room_group": "11332.16",
 *                     "pay_at_hotel_available": "",
 *                     "pay_now_available": "",
 *                     "non_refundable_rates": "387590766,382751269",
 *                     "refundable_rates": "383644725,383644711",
 *                     "room_groups": {
 *                         {
 *                             "total_price": 11332.16,
 *                             "total_tax": 1888.68,
 *                             "total_fees": 0,
 *                             "total_net": 9443.48,
 *                             "affiliate_service_charge": 0,
 *                             "currency": "USD",
 *                             "pay_now": false,
 *                             "pay_at_hotel": false,
 *                             "non_refundable": true,
 *                             "meal_plan": "",
 *                             "rate_id": 382751269,
 *                             "rate_description": "",
 *                             "cancellation_policies": {
 *                                 {
 *                                     "start": "2023-11-03T12:59:00.165+00:00",
 *                                     "end": "2023-11-11T17:00:00.000+00:00",
 *                                     "percent": "100%",
 *                                     "currency": "USD"
 *                                 }
 *                             },
 *                             "opaque": false,
 *                             "rooms": {
 *                                 {
 *                                     "giata_room_code": "",
 *                                     "giata_room_name": "",
 *                                     "supplier_room_name": "Junior Suite",
 *                                     "per_day_rate_breakdown": "",
 *                                     "total_price": 12591.32,
 *                                     "total_tax": 2098.54,
 *                                     "total_fees": 0,
 *                                     "total_net": 10492.78,
 *                                     "affiliate_service_charge": 0,
 *                                     "booking_item": "bfc54dd3-1ca5-4624-98cc-0d1ab8d8f153"
 *                                 },
 *                                 {
 *                                     "giata_room_code": "",
 *                                     "giata_room_name": "",
 *                                     "supplier_room_name": "Junior Suite",
 *                                     "per_day_rate_breakdown": "",
 *                                     "total_price": 14034.31,
 *                                     "total_tax": 2339.05,
 *                                     "total_fees": 0,
 *                                     "total_net": 11695.26,
 *                                     "affiliate_service_charge": 0,
 *                                     "booking_item": "871198e4-d02f-47f5-9cb7-b52abf20ae93"
 *                                 },
 *                                 {
 *                                     "giata_room_code": "",
 *                                     "giata_room_name": "",
 *                                     "supplier_room_name": "Junior Suite",
 *                                     "per_day_rate_breakdown": "",
 *                                     "total_price": 12630.85,
 *                                     "total_tax": 2105.14,
 *                                     "total_fees": 0,
 *                                     "total_net": 10525.71,
 *                                     "affiliate_service_charge": 0,
 *                                     "booking_item": "3125f606-c4b2-4795-ac35-9597fa92b4d4"
 *                                 },
 *                                 {
 *                                     "giata_room_code": "",
 *                                     "giata_room_name": "",
 *                                     "supplier_room_name": "Junior Suite",
 *                                     "per_day_rate_breakdown": "",
 *                                     "total_price": 11332.16,
 *                                     "total_tax": 1888.68,
 *                                     "total_fees": 0,
 *                                     "total_net": 9443.48,
 *                                     "affiliate_service_charge": 0,
 *                                     "booking_item": "7f9dd9b9-3812-4077-8288-0cb5bb1b1e32"
 *                                 }
 *                             }
 *                         }
 *                     },
 *                     "weight": 0
 *                 }
 *             }
 *         },
 *         "search_id": "d740b649-f583-48d3-91fd-cdf4c58b14dc"
 *     },
 *     "message": "success"
 * }
 * )
 */




class PricingSearchResponse
{
}
