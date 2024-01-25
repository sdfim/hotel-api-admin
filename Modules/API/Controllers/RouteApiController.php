<?php

namespace Modules\API\Controllers;

use App\Http\Controllers\Controller;
use App\Models\GeneralConfiguration;
use App\Models\GiataGeography;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Modules\API\Controllers\ApiHandlers\ComboApiHandler;
use Modules\API\Controllers\ApiHandlers\FlightApiHandler;
use Modules\API\Controllers\ApiHandlers\HotelApiHandler;
use Modules\Enums\RouteEnum;
use Modules\Enums\TypeRequestEnum;

class RouteApiController extends Controller
{
    /**
     * @param Request $request
     * @return mixed
     */
    public function handle(Request $request): mixed
    {
        $type = $request->get('type');
        $route = Route::currentRouteName();

        if (!$this->isTypeValid($type)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid type',
            ], 400);
        }
        if (!$this->isRouteValid($route)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid route',
            ], 400);
        }

        $suppliersIds = GeneralConfiguration::pluck('currently_suppliers')->first() ?? [1];

        $dataHandler = match ($type) {
            'hotel' => new HotelApiHandler(),
            'flight' => new FlightApiHandler(),
            'combo' => new ComboApiHandler(),
            default => response()->json(['error' => 'Invalid route'], 400),
        };

        return match ($route) {
            'search' => $dataHandler->search($request),
            'detail' => $dataHandler->detail($request),
            'price' => $dataHandler->price($request, $suppliersIds),
            default => response()->json(['error' => 'Invalid route'], 400),
        };
    }

    /**
     * @OA\Get(
     *   tags={"Content API"},
     *   path="/api/content/destinations",
     *   summary="Get list of destinations",
     *   description="Get list valid value of destinations by city name, can be used for autocomplete, min 3 characters",
     *
     *    @OA\Parameter(
     *      name="city",
     *      in="query",
     *      required=true,
     *      description="Type of content to search (e.g., 'rome', 'new y', londo').",
     *
     *      @OA\Schema(
     *        type="string",
     *        example="londo"
     *        )
     *    ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *
     *     @OA\JsonContent(
     *       ref="#/components/schemas/ContentDestinationslResponse",
     *       examples={
     *       "example1": @OA\Schema(ref="#/components/examples/ContentDestinationslResponse", example="ContentDestinationslResponse"),
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
    public function destinations(Request $request): JsonResponse
    {
        if (empty($request->get('city'))) {
            return response()->json(['error' => 'Invalid city'], 400);
        }
        if (strlen($request->get('city')) < 3) {
            return response()->json(['error' => 'Invalid city, string must be 3 characters or more'], 400);
        }

        $giataGeography = GiataGeography::select(DB::raw('CONCAT(city_name, ", ", country_name, " (", country_code, ", ", locale_name, ")") AS full_name'), 'city_id')
            ->where('city_name', 'like', $request->get('city') . '%')
            ->limit(35)
            ->orderBy('city_id', 'asc')
            ->get()
            ->pluck('city_id', 'full_name')
            ->toArray();

        $destinations = [];
        foreach ($giataGeography as $key => $value) {
            $destinations[] = [
                'full_name' => $key,
                'city_id' => $value,
            ];
        }

        $response = [
            'success' => true,
            'data' => $destinations,
        ];

        return response()->json($response);
    }

    /**
     * @param $value
     * @return bool
     */
    private function isTypeValid($value): bool
    {
        $values = array_map(function($case) {
            return $case->value;
        }, TypeRequestEnum::cases());
        return in_array($value, $values, true);
    }

    /**
     * @param $value
     * @return bool
     */
    public function isRouteValid($value): bool
    {
        $values = array_map(function($case) {
            return $case->value;
        }, RouteEnum::cases());
        return in_array($value, $values, true);    }
}
