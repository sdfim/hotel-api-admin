<?php

namespace Modules\API\Controllers;

use App\Http\Controllers\Controller;
use App\Models\GeneralConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\API\Controllers\ApiHandlers\ComboApiHandler;
use Modules\API\Controllers\ApiHandlers\FlightApiHandler;
use Modules\API\Controllers\ApiHandlers\HotelApiHandler;
use Modules\API\Controllers\ApiHandlers\HotelApiHandlerV1;
use Modules\API\Requests\DetailHotelRequest;
use Modules\API\Requests\PriceHotelRequest;
use Modules\API\Requests\SearchHotelRequest;
use Modules\Enums\RouteEnum;
use Modules\Enums\TypeRequestEnum as TypeEnum;

class RouteApiController extends Controller
{
    public function __construct(
        private HotelApiHandler $hotelApiHandler,
        private FlightApiHandler $flightApiHandler,
        private ComboApiHandler $comboApiHandler,
        private HotelApiHandlerV1 $hotelApiHandlerV1,
    ) { }

    /**
     * @throws \Throwable
     */
    public function handle(Request $request): mixed
    {
        $type = $request->type;
        $route = Route::currentRouteName();

        if (! $this->isTypeValid($type)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid type',
            ], 400);
        }
        if (! $this->isRouteValid($route)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid route',
            ], 400);
        }

        $suppliersIds = GeneralConfiguration::pluck('currently_suppliers')->first() ?? [1];

        $handler = match (TypeEnum::from($type)) {
            TypeEnum::HOTEL => str_contains($route, 'v1') ? $this->hotelApiHandlerV1 : $this->hotelApiHandler,
            TypeEnum::FLIGHT => $this->flightApiHandler,
            TypeEnum::COMBO => $this->comboApiHandler,
        };

        return match (RouteEnum::from($route)) {
            RouteEnum::ROUTE_SEARCH, RouteEnum::ROUTE_SEARCH_V1 => $handler->search($this->searchRequest($type)),
            RouteEnum::ROUTE_DETAIL, RouteEnum::ROUTE_DETAIL_V1 => $handler->detail($this->detailRequest($type)),
            RouteEnum::ROUTE_PRICE, RouteEnum::ROUTE_PRICE_V1 => $handler->price($this->priceRequest($type), $suppliersIds),
        };
    }

    private function searchRequest(string $type): Request
    {
        return match (TypeEnum::from($type)) {
            TypeEnum::HOTEL => resolve(SearchHotelRequest::class),
            default => resolve(Request::class),
        };
    }

    private function detailRequest(string $type): Request
    {
        return match (TypeEnum::from($type)) {
            TypeEnum::HOTEL => resolve(DetailHotelRequest::class),
            default => resolve(Request::class),
        };
    }

    private function priceRequest(string $type): Request
    {
        return match (TypeEnum::from($type)) {
            TypeEnum::HOTEL => resolve(PriceHotelRequest::class),
            default => resolve(Request::class),
        };
    }

    private function isTypeValid($value): bool
    {
        $values = array_map(function ($case) {
            return $case->value;
        }, TypeEnum::cases());

        return in_array($value, $values, true);
    }

    public function isRouteValid($value): bool
    {
        $values = array_map(function ($case) {
            return $case->value;
        }, RouteEnum::cases());

        return in_array($value, $values, true);
    }
}
