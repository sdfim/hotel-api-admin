<?php

namespace Modules\API\Resources\Booking;

/**
 * @OA\Schema(
 *   schema="NotFoundResponse",
 *   type="object",
 *   required={"message"},
 *   @OA\Property(
 *     property="message",
 *     type="string",
 *     example="Resource not found"
 *   )
 * )
 *
 * @OA\Examples(
 *   example="NotFoundResponse",
 *   summary="Example of NotFoundResponse",
 *   value={
 *     "message": "Resource not found"
 *   }
 * )
 */
class NotFoundResponse
{
}
