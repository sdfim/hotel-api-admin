<?php

namespace Modules\API\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\API\Controllers\ApiHandlers\Reports\ReportsApiHandler;
use Modules\API\Requests\BookingsReportRequest;
use Modules\Enums\RouteReportEnum;

class RouteReportApiController extends Controller
{
    public function handle(Request $request): mixed
    {
        $route = Route::currentRouteName();

        if (! $this->isRouteValid($route)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid route',
            ], 400);
        }


        return match (RouteReportEnum::from($route)) {
            RouteReportEnum::ROUTE_BOOKINGS => app(ReportsApiHandler::class)->bookings(resolve(BookingsReportRequest::class)),
        };
    }

    public function isRouteValid($value): bool
    {
        $values = array_map(function ($case) {
            return $case->value;
        }, RouteReportEnum::cases());

        return in_array($value, $values, true);
    }
}
