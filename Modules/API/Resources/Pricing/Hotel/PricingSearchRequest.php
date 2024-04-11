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
 *     description="Checkin date in ISO 8601 format (e.g., '2024-11-11').",
 *     type="string",
 *     format="date",
 *     example="2024-11-11"
 *   ),
 *   @OA\Property(
 *     property="checkout",
 *     description="Checkout date in ISO 8601 format (e.g., '2024-11-20').",
 *     type="string",
 *     format="date",
 *     example="2024-11-20"
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
 *        example="PricingSearchRequestPlaceCancun",
 *        summary="Pricing Search Cancun, country MX, airport CUN using Place",
 *        value=
 *    {
 *        "type": "hotel",
 *        "checkin": "2024-05-11",
 *        "checkout": "2024-05-20",
 *        "place": "4b2afe6684dd81a6f73527468e05e7a6",
 *        "rating": 4.5,
 *        "occupancy": {
 *            {
 *               "adults": 2
 *            }
 *        }
 *    }
 *    ),
 * @OA\Examples(
 *       example="PricingSearchRequestPlace",
 *       summary="Pricing Search Eiffel Tower (Paris) using Place",
 *       value=
 *   {
 *       "type": "hotel",
 *       "checkin": "2024-05-11",
 *       "checkout": "2024-05-20",
 *       "place": "9bb07983384eb956ae88e154b99b51a1",
 *       "rating": 4.5,
 *       "occupancy": {
 *           {
 *              "adults": 2
 *           }
 *       }
 *   }
 *   ),
 * @OA\Examples(
 *     example="PricingSearchRequestLondon",
 *     summary="Pricing Search London using Destination",
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
 * @OA\Examples(
 *      example="PricingSearchRequestNewYork",
 *      summary="Pricing Search New York using Destination",
 *      value=
 *  {
 *      "type": "hotel",
 *      "checkin": "2024-05-11",
 *      "checkout": "2024-05-20",
 *      "destination": 961,
 *      "rating": 4.5,
 *      "occupancy": {
 *          {
 *             "adults": 2,
 *           "children_ages": {
 *             2
 *            }
 *          },
 *        {
 *             "adults": 2
 *          }
 *      }
 *  }
 *  ),
 * @OA\Examples(
 *       example="PricingSearchRequestCancun",
 *       summary="Pricing Search Cancun using Destination",
 *       value=
 *   {
 *       "type": "hotel",
 *       "checkin": "2024-05-11",
 *       "checkout": "2024-05-20",
 *       "destination": 508,
 *       "rating": 4.5,
 *       "occupancy": {
 *           {
 *              "adults": 2
 *           }
 *       }
 *   }
 *   ),
 * * @OA\Examples(
 *     example="PricingSearchRequestCurrencySupplier",
 *     summary="Pricing Search set Supplier",
 *     value=
 * {
 *   "type": "hotel",
 *   "currency": "USD",
 *   "supplier": "Expedia",
 *   "checkin": "2024-06-19",
 *   "checkout": "2024-06-21",
 *   "destination": 951,
 *   "rating": 4.5,
 *   "occupancy": {
 *     {
 *       "adults": 2,
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
