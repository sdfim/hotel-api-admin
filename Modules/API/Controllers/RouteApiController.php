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
use Modules\API\Requests\DestinationResponse;
use Modules\API\Requests\DetailHotelRequest;
use Modules\API\Requests\PriceHotelRequest;
use Modules\API\Requests\SearchHotelRequest;
use Modules\Enums\RouteEnum;
use Modules\Enums\TypeRequestEnum as TypeEnum;

class RouteApiController extends Controller
{
    /**
     * @param Request $request
     * @return mixed
     * @throws \Throwable
     */
    public function handle(Request $request): mixed
    {
        $type = $request->type;
        $route = Route::currentRouteName();

        if (!$this->isTypeValid($type)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid type',
            ], 400);
        }
        if (!$this->isRouteValid($route)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid route',
            ], 400);
        }

        $suppliersIds = GeneralConfiguration::pluck('currently_suppliers')->first() ?? [1];

        $handler = match (TypeEnum::from($type)) {
            TypeEnum::HOTEL => new HotelApiHandler(),
            TypeEnum::FLIGHT => new FlightApiHandler(),
            TypeEnum::COMBO => new ComboApiHandler(),
        };

        return match (RouteEnum::from($route)) {
            RouteEnum::ROUTE_SEARCH => $handler->search($this->searchRequest($type)),
            RouteEnum::ROUTE_DETAIL => $handler->detail($this->detailRequest($type)),
            RouteEnum::ROUTE_PRICE => $handler->price($this->priceRequest($type), $suppliersIds),
        };
    }

    /**
     * @param string $type
     * @return Request
     */
    private function searchRequest(string $type): Request
    {
        return match (TypeEnum::from($type)) {
            TypeEnum::HOTEL => resolve(SearchHotelRequest::class),
            default => resolve(Request::class),
        };
    }

    /**
     * @param string $type
     * @return Request
     */
    private function detailRequest(string $type): Request
    {
        return match (TypeEnum::from($type)) {
            TypeEnum::HOTEL => resolve(DetailHotelRequest::class),
            default => resolve(Request::class),
        };
    }

    /**
     * @param string $type
     * @return Request
     */
    private function priceRequest(string $type): Request
    {
        return match (TypeEnum::from($type)) {
            TypeEnum::HOTEL => resolve(PriceHotelRequest::class),
            default => resolve(Request::class),
        };
    }

    /**
     * @param DestinationResponse $request
     * @return JsonResponse
     */
    public function destinations(DestinationResponse $request): JsonResponse
    {
        $giataGeography = GiataGeography::select(DB::raw('CONCAT(city_name, ", ", country_name, " (", country_code, ", ", locale_name, ")") AS full_name'), 'city_id')
            ->where('city_name', 'like', $request->city . '%')
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
        $values = array_map(function ($case) {
            return $case->value;
        }, TypeEnum::cases());
        return in_array($value, $values, true);
    }

    /**
     * @param $value
     * @return bool
     */
    public function isRouteValid($value): bool
    {
        $values = array_map(function ($case) {
            return $case->value;
        }, RouteEnum::cases());
        return in_array($value, $values, true);
    }
}
