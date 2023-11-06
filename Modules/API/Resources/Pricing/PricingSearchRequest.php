<?php

namespace Modules\API\Resources\Pricing;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="PricingSearchRequest",
 *   title="Pricing Search Request",
 *   description="Pricing Search Request",
 *   type="object",
 *   required={"type", "checkin", "checkout", "destination", "occupancy"},
 *   @OA\Property(
 *     property="type",
 *     type="string",
 *     description="Type of content to search (e.g., 'hotel', 'flight', 'combo').",
 *     enum={"hotel", "flight", "combo"},
 *     example="hotel"
 *   ),
 *   @OA\Property(
 *     property="checkin",
 *     description="Checkin date in ISO 8601 format (e.g., '2023-11-11').",
 *     type="string",
 *     format="date",
 *     example="2023-11-11"
 *   ),
 *   @OA\Property(
 *     property="checkout",
 *     description="Checkout date in ISO 8601 format (e.g., '2023-11-20').",
 *     type="string",
 *     format="date",
 *     example="2023-11-20"
 *   ),
 *   @OA\Property(
 *     property="destination",
 *     description="Destination ID (e.g., '961' is New York, referral code can be viewed '/api/content/destinations?city={name city}' ).",
 *     type="integer",
 *     example="961"
 *   ),
 *   @OA\Property(
 *     property="rating",
 *     description="Rating of the hotel (e.g., '4.5').",
 *     type="number",
 *     enum={1.0, 1.5, 2.0, 2.5, 3.0, 3.5, 4.0, 4.5, 5.0, 5.5},
 *   ),
 *   @OA\Property(
 *     property="occupancy",
 *     description="Occupancy of the hotel",
 *     type="array",
 *     @OA\Items(
 *       type="object",
 *       required={"adults"},
 *       @OA\Property(
 *         property="adults",
 *         description="Number of adults (e.g., '2').",
 *         type="integer",
 *         example="2"
 *       ),
 *       @OA\Property(
 *         property="children",
 *         description="Number of children (e.g., '2').",
 *         type="integer",
 *         example="2"
 *       )
 *     )
 *   )
 * ),
 * @OA\Examples(
 *     example="PricingSearchRequestNewYork",
 *     summary="An Example Pricing Search Request New York",
 *     value=
 * {
 * 	 "type": "hotel",
 * 	 "checkin": "2023-11-11",
 * 	 "checkout": "2023-11-20",
 * 	 "destination": 961,
 * 	 "rating": 4.5,
 * 	 "occupancy": {
 * 		 {
 * 			"adults": 4,
 *          "children": 1
 * 		 },
 *       {
 * 			"adults": 3
 * 		 }
 * 	 }
 * }
 * ),
 * @OA\Examples(
 *     example="PricingSearchRequestLondon",
 *     summary="An Example Pricing Search RequestLondon",
 *     value=
 * {
 * 	 "type": "hotel",
 * 	 "checkin": "2023-11-11",
 * 	 "checkout": "2023-11-20",
 * 	 "destination": 302,
 * 	 "rating": 4,
 * 	 "occupancy": {
 * 		 {
 * 			"adults": 2,
 *          "children": 2
 * 		 },
 *       {
 * 			"adults": 3
 * 		 }
 * 	 }
 * }
 * ),
 * * @OA\Examples(
 *     example="PricingSearchRequestOccupavcyWithOutChildren",
 *     summary="Occupavcy Without Children",
 *     value=
 * {
 * 	 {
 * 	 "adults": 2
 * 	 },
 *   {
 * 	 "adults": 3
 * 	 },
 *   {
 * 	 "adults": 4
 * 	 }
 * }
 * ),
 * * @OA\Examples(
 *     example="PricingSearchRequestOccupavcyWithChildren",
 *     summary="Occupavcy With Children",
 *     value=
 * {
 * 	 {
 * 	 "adults": 2,
 *   "children": 2
 * 	 },
 *   {
 * 	 "adults": 3
 * 	 }
 * }
 * )
 */

class PricingSearchRequest
{
}
