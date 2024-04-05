<?php

namespace Modules\API\Resources\Pricing\Hotel;

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
 *     property="currency",
 *     description="Currency of the hotel/flight/combo (e.g., 'EUR').",
 *     type="string",
 *     enum={"AED", "ARS", "AUD", "BRL", "CAD", "CHF", "CNY", "COP", "DKK", "EGP", "EUR", "GBP", "HKD", "IDR", "ILS", "INR", "JPY", "KRW", "LBP", "MAD", "MXN", "MYR", "NOK", "NZD", "PHP", "PLN", "QAR", "RUB", "SAR", "SEK", "SGD", "THB", "TRY", "TWD", "USD", "VND", "ZAR"},
 *     example="EUR"
 *   ),
 *   @OA\Property(
 *     property="supplier",
 *     description="Supplier of the hotel/flight/comb (e.g., 'Expedia').",
 *     type="string",
 *     enum={"Expedia", "Example"},
 *     example="Expedia"
 *   ),
 *   @OA\Property(
 *     property="hotel_name",
 *     description="Name of the hotel (e.g., 'Sheraton').",
 *     type="string",
 *     example="Sheraton"
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
 *       ),
 *       @OA\Property(
 *         property="children_ages",
 *         description="Ages of children (e.g., '2, 4').",
 *         type="array",
 *         @OA\Items(
 *           type="integer",
 *           example="2"
 *         )
 *       )
 *     )
 *   )
 * ),
 * @OA\Examples(
 *     example="PricingSearchRequestNewYork",
 *     summary="An Example Pricing Search Request New York",
 *     value=
 * {
 *     "type": "hotel",
 *     "checkin": "2024-05-11",
 *     "checkout": "2024-05-20",
 *     "destination": 961,
 *     "rating": 4.5,
 *     "occupancy": {
 *         {
 *            "adults": 2,
 *          "children_ages": {
 *            2
 *           }
 *         },
 *       {
 *            "adults": 2
 *         }
 *     }
 * }
 * ),
 * @OA\Examples(
 *      example="PricingSearchRequestCancun",
 *      summary="An Example Pricing Search Request Cancun",
 *      value=
 *  {
 *      "type": "hotel",
 *      "checkin": "2024-05-11",
 *      "checkout": "2024-05-20",
 *      "destination": 961,
 *      "rating": 4.5,
 *      "occupancy": {
 *          {
 *             "adults": 2
 *          }
 *      }
 *  }
 *  ),
 * @OA\Examples(
 *     example="PricingSearchRequestLondon",
 *     summary="An Example Pricing Search RequestLondon",
 *     value=
 * {
 *     "type": "hotel",
 *     "checkin": "2024-05-01",
 *     "checkout": "2024-05-05",
 *     "destination": 302,
 *     "rating": 4,
 *     "occupancy": {
 *         {
 *            "adults": 2,
 *          "children_ages": {
 *            2,
 *            4
 *           }
 *         },
 *       {
 *            "adults": 1
 *         }
 *     }
 * }
 * ),
 * * @OA\Examples(
 *     example="PricingSearchRequestCurrencySupplier",
 *     summary="Occupavcy With Currency and Supplier",
 *     value=
 * {
 *   "type": "hotel",
 *   "currency": "EUR",
 *   "supplier": "Expedia",
 *   "hotel_name": "Sheraton",
 *   "checkin": "2023-11-19",
 *   "checkout": "2023-11-21",
 *   "destination": 961,
 *   "rating": 3.5,
 *   "occupancy": {
 *     {
 *       "adults": 2,
 *       "children": 1,
 *       "children_ages": {
 *         2
 *        }
 *     },
 *     {
 *       "adults": 1
 *     }
 *   }
 * }
 * )
 */
class PricingSearchRequest
{
}
