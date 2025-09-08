<?php

namespace Modules\API\Requests;

use Modules\API\Validate\ApiRequest;

class DestinationRequest extends ApiRequest
{
    /**
     * @OA\Get(
     *   tags={"Content API"},
     *   path="/api/content/destinations",
     *   summary="Get list of destinations, hotels, or autocomplete suggestions",
     *   description="Returns a list of destinations, hotels, or autocomplete suggestions based on the provided query parameters. At least one of: hotel, city, country, giata, or q must be provided (min 3 characters).<br>Supports Google autocomplete if strategy=Google and giata is not set.<br>Hotel search supports pagination.",
     *
     *     @OA\Parameter(
     *       name="hotel",
     *       in="query",
     *       required=false,
     *       description="Hotel name to search (min 3 characters).",
     *       @OA\Schema(type="string", minLength=3, example="Hilton")
     *     ),
     *     @OA\Parameter(
     *       name="city",
     *       in="query",
     *       required=false,
     *       description="City name to search (min 3 characters).",
     *       @OA\Schema(type="string", minLength=3, example="Paris")
     *     ),
     *     @OA\Parameter(
     *       name="country",
     *       in="query",
     *       required=false,
     *       description="Country name to search (min 3 characters).",
     *       @OA\Schema(type="string", minLength=3, example="France")
     *     ),
     *     @OA\Parameter(
     *       name="giata",
     *       in="query",
     *       required=false,
     *       description="Giata code to search (min 3 characters).",
     *       @OA\Schema(type="string", minLength=3, example="12345")
     *     ),
     *     @OA\Parameter(
     *       name="q",
     *       in="query",
     *       required=false,
     *       description="General query for places/destinations (min 3 characters).",
     *       @OA\Schema(type="string", minLength=3, example="Eiffel")
     *     ),
     *     @OA\Parameter(
     *       name="strategy",
     *       in="query",
     *       required=false,
     *       description="Define strategy for the search suggestions (Default or Google).",
     *       @OA\Schema(type="string", enum={"Default","Google"}, default="Default")
     *     ),
     *     @OA\Parameter(
     *       name="showtticodes",
     *       in="query",
     *       required=false,
     *       description="Set to 1 to display additional tticodes.",
     *       @OA\Schema(type="integer", enum={0, 1}, default=0)
     *     ),
     *     @OA\Parameter(
     *       name="page",
     *       in="query",
     *       required=false,
     *       description="Page number for hotel search pagination.",
     *       @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *       name="per_page",
     *       in="query",
     *       required=false,
     *       description="Number of results per page for hotel search pagination.",
     *       @OA\Schema(type="integer", default=10)
     *     ),
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *       oneOf={
     *         @OA\Schema(ref="#/components/schemas/ContentDestinationslResponse"),
     *         @OA\Schema(
     *           type="object",
     *           example={"success":true,"data":{"hotels":{{"name":"Hilton","giata_code":"12345"}},"total":1,"page":1,"per_page":10}}
     *         ),
     *         @OA\Schema(
     *           type="object",
     *           example={"success":true,"data":{{"name":"Eiffel Tower","type":"poi"}}}
     *         )
     *       }
     *     )
     *   ),
     *   @OA\Response(
     *     response=400,
     *     description="Bad Request",
     *     @OA\JsonContent(ref="#/components/schemas/BadRequestResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated",
     *     @OA\JsonContent(ref="#/components/schemas/UnAuthenticatedResponse")
     *   ),
     *   security={{ "apiAuth": {} }}
     * )
     */
    public function rules(): array
    {
        return [
            'hotel' => 'required_without_all:city,country,q|string|min:3',

            'city' => 'required_without_all:country,q,giata,hotel|string|min:3',
            'country' => 'required_without_all:city,q,giata,hotel|string|min:3',
            'giata' => 'required_without_all:city,country,q,hotel|string|min:3',

            'include' => 'nullable|array',
            'q' => 'required_without_all:city,country,giata,hotel|string|min:3',
            'strategy' => 'nullable|string|in:Default,Google',
        ];
    }

    public function validatedDate(): array
    {
        return parent::validated();
    }
}
