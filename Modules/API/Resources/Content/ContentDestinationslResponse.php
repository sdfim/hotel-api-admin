<?php

namespace Modules\API\Resources\Content;

/**
 * @OA\Schema(
 *   schema="ContentDestinationslResponse",
 *   title="Content Destinations Response",
 *   description="Schema Content Destinations Response",
 *   type="object",
 *   required={"success", "data"},
 *
 *   @OA\Property(
 *     property="success",
 *     type="boolean",
 *     description="Indicates the success status of the response.",
 *     example=true
 *   ),
 *   @OA\Property(
 *     property="data",
 *     type="object",
 *     description="Data of the response.",
 *     @OA\Property(
 *       property="results",
 *       type="object",
 *       description="Results of the response.",
 *       @OA\Property(
 *         property="full_name",
 *         type="string",
 *         description="Full name of the destination.",
 *         example="London, Canada (CA, Ontario)"
 *       ),
 *       @OA\Property(
 *         property="city_id",
 *         type="integer",
 *         description="City ID of the destination.",
 *         example=14742
 *       )
 *     )
 *   )
 * ),
 *
 * @OA\Examples(
 *     example="ContentDestinationslResponse",
 *     summary="Content Destinations Response",
 *     value=
 * {
 *    "success": true,
 *    "data": {
 *        {
 *            "full_name": "London, Canada (CA, Ontario)",
 *            "city_id": 14742
 *        },
 *        {
 *            "full_name": "London, United Kingdom (GB, London & surrounding area)",
 *            "city_id": 302
 *        },
 *        {
 *            "full_name": "London, USA (US, Kentucky)",
 *            "city_id": 20714
 *        },
 *        {
 *            "full_name": "London, USA (US, Ohio)",
 *            "city_id": 34798
 *        },
 *        {
 *            "full_name": "Londonderry, United Kingdom (GB, Northern Ireland)",
 *            "city_id": 12388
 *        },
 *        {
 *            "full_name": "Londonderry, USA (US, New Hampshire)",
 *            "city_id": 27346
 *        },
 *        {
 *            "full_name": "Londonderry, USA (US, Vermont)",
 *            "city_id": 41694
 *        }
 *    }
 * }
 * ),
 * @OA\Examples(
 *      example="ContentDestinationslResponseEiffel",
 *      summary="Content Destinations Response Eiffel Tower",
 *      value=
 *  {
 *     "success": true,
 *     "data": {
 *         {
 *          "full_name": "Eiffel Tower (Paris Region), country FR, airport PAR",
 *          "place": "451866aafc2cd344ca5091dc267cb802",
 *          "type": "Landmark"
 *          },
 *          {
 *          "full_name": "Eiffel Tower (Paris), country FR, airport PAR",
 *          "place": "9bb07983384eb956ae88e154b99b51a1",
 *          "type": "Landmark"
 *          },
 *          {
 *          "full_name": "Eiffel Tower (Central Paris), country FR, airport PAR",
 *          "place": "ed4f0cef597a1e903856b30e881f4462",
 *          "type": "Landmark"
 *          },
 *          {
 *          "full_name": "Eiffel Tower (Paris 07e Arrondissement), country FR, airport PAR",
 *          "place": "7cf40f7c174443ce6fec02704f670870",
 *          "type": "Landmark"
 *          }
 *     }
 *  }
 *  ),
 * @OA\Examples(
 *       example="ContentDestinationslResponseTurks",
 *       summary="Content Destinations Response Turks and",
 *       value=
 *   {
 *      "success": true,
 *      "data": {
 *          {
 *          "full_name": "Turks & Caicos Islands, country TC, airport PLS",
 *          "place": "e90b74d1eb220d4dc290ce968fefebac",
 *          "type": "Country"
 *          }
 *      }
 *   }
 *   ),
 * @OA\Examples(
 *        example="ContentDestinationslResponseStLucia",
 *        summary="Content Destinations Response St Lucia",
 *        value=
 *    {
 *       "success": true,
 *       "data": {
 *           {
 *              "full_name": "St Lucia, country ZA, airport DUR",
 *              "place": "d47e3a5fc397ff31d20924d2841caedc",
 *              "type": "City"
 *              },
 *              {
 *              "full_name": "St Lucia, country LC, airport UVF",
 *              "place": "29695f360d0556f0a644594e6b476843",
 *              "type": "Country"
 *              }
 *       }
 *    }
 *  ),
 * @OA\Examples(
 *         example="ContentDestinationslResponseTowerPisa",
 *         summary="Content Destinations Response Tower Pisa and showtticodes",
 *         value=
 *     {
 *        "success": true,
 *        "data": {
 *            {
 *              "full_name": "Leaning Tower of Pisa, country IT, airport PSA",
 *              "place": "b05c2d0dec89321b9ae8c6e14c6fdc5a",
 *              "type": "Landmark",
 *              "tticodes": {
 *                   "11872855",
 *                   "12108455",
 *                   "18774833",
 *                   "26633462",
 *                   "31163051",
 *                   "40563302",
 *                   "47958637",
 *                   "48679622",
 *                   "59324077",
 *                   "62547051",
 *                   "62592753",
 *                   "69860215",
 *                   "77578695",
 *                   "77907437",
 *                   "81799837",
 *                   "86293211"
 *                   }
 *              }
 *        }
 *     }
 *   ),
 */
class ContentDestinationslResponse
{
}
