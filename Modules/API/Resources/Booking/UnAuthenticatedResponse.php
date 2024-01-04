<?php

namespace Modules\API\Resources\Booking;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="UnAuthenticatedResponse",
 *   title="UnAuthenticated 401 error",
 *   description="Schema of UnAuthenticated 401 error",
 *   type="object",
 *   required={"api_status", "message"},
 *   @OA\Property(
 *     property="api_status",
 *     type="integer",
 *     description="API status code",
 *     example=401
 *   ),
 *   @OA\Property(
 *     property="message",
 *     type="string",
 *     description="Error message",
 *     example="UnAuthenticated"
 *   )
 * ),
 * @OA\Examples(
 *     example="UnAuthenticatedResponse",
 *     summary="Example of UnAuthenticated",
 *     value=
 * {
 *    "api_status": "401",
 *    "message": "UnAuthenticated"
 * }
 * )
 */
class UnAuthenticatedResponse
{
}
