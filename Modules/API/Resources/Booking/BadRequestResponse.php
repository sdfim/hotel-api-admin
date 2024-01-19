<?php

namespace Modules\API\Resources\Booking;

/**
 * @OA\Schema(
 *   schema="BadRequestResponse",
 *   title="BadRequest 400 error",
 *   description="Schema of BadRequest 400 error",
 *   type="object",
 *   required={"message"},
 *   @OA\Property(
 *     property="message",
 *     type="string",
 *     description="Error message",
 *     example="Invalid type"
 *   )
 * ),
 * @OA\Examples(
 *     example="BadRequestResponse",
 *     summary="Example of BadRequest",
 *     value=
 * {
 *     "message": "Invalid type"
 * }
 * )
 */
class BadRequestResponse
{
}
