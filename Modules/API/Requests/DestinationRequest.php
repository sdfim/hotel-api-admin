<?php

namespace Modules\API\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Modules\API\Validate\ApiRequest;

class DestinationRequest extends ApiRequest
{
    /**
     * @OA\Get(
     *   tags={"Content API"},
     *   path="/api/content/destinations",
     *   summary="Get list of destinations",
     *   description="Get list valid value of places/destinations by city name, can be used for autocomplete, min 3 characters<br> The Response results (places/destinations) can be used as valid values on endpoints:<br>     *      <b>api/pricing/search<br>
     *      api/pricing/search</b>",
     *
     *     @OA\Parameter(
     *       name="q",
     *       in="query",
     *       required=true,
     *       description="Type of content to search (e.g., 'Turks and', 'Eiffel', Nassau', 'St Lucia', 'UVF', Cancun').",
     *
     *       @OA\Schema(
     *         type="string",
     *         example="Eiffel"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *        name="showtticodes",
     *        in="query",
     *        required=false,
     *        description="Set to 1 to display additional tticodes.",
     *
     *        @OA\Schema(
     *          type="integer",
     *          enum={0, 1},
     *          default=0
     *        )
     *      ),
     *
     *     @OA\Parameter(
     *        name="strategy",
     *        in="query",
     *        required=false,
     *        description="Define strategy for the search suggestions.",
     *
     *        @OA\Schema(
     *          type="string",
     *          enum={"Default", "Google"},
     *          default="Default"
     *        )
     *      ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/ContentDestinationslResponse",
     *       examples={
     *          "example1": @OA\Schema(ref="#/components/examples/ContentDestinationslResponseEiffel", example="ContentDestinationslResponseEiffel"),
     *          "example2": @OA\Schema(ref="#/components/examples/ContentDestinationslResponseTurks", example="ContentDestinationslResponseTurks"),
     *          "example3": @OA\Schema(ref="#/components/examples/ContentDestinationslResponseStLucia", example="ContentDestinationslResponseStLucia"),
     *          "example4": @OA\Schema(ref="#/components/examples/ContentDestinationslResponseTowerPisa", example="ContentDestinationslResponseTowerPisa"),
     *          "example5": @OA\Schema(ref="#/components/examples/ContentDestinationslResponse", example="ContentDestinationslResponse"),
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/BadRequestResponse",
     *       examples={
     *       "example1": @OA\Schema(ref="#/components/examples/BadRequestResponse", example="BadRequestResponse"),
     *       }
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/UnAuthenticatedResponse",
     *       examples={
     *       "example1": @OA\Schema(ref="#/components/examples/UnAuthenticatedResponse", example="UnAuthenticatedResponse"),
     *       }
     *     )
     *   ),
     *   security={{ "apiAuth": {} }}
     * )
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'city' => 'required_without_all:country,q,giata|string|min:3',
            'country' => 'required_without_all:city,q,giata|string|min:3',
            'giata' => 'required_without_all:city,country,q|string|min:3',
            'include' => 'nullable|array',
            'q' => 'required_without_all:city,country,giata|string|min:3',
            'strategy' => 'nullable|string|in:Default,Google',
        ];
    }

    public function validatedDate(): array
    {
        return parent::validated();
    }
}
