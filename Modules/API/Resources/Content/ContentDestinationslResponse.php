<?php

namespace Modules\API\Resources\Content;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="ContentDestinationslResponse",
 *   title="Content Destinations Response",
 *   description="Schema Content Destinations Response",
 *   type="object",
 *   required={"success", "data"},
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
 * @OA\Examples(
 *     example="ContentDestinationslResponse",
 *     summary="Example Content Destinations Response",
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
 * )
 */
class ContentDestinationslResponse
{
}
