<?php

namespace Modules\API\Controllers;

use App\Http\Controllers\Controller;
use App\Models\GiataProperty;
use App\Models\GiataGeography;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Modules\API\Controllers\ApiHandlers\HotelApiHanlder;
use Modules\API\Controllers\ApiHandlers\FlightApiHandler;
use Modules\API\Controllers\ApiHandlers\ComboApiHandler;
use Modules\API\Suppliers\ExpediaSupplier\ExpediaService;

class RouteApiController extends Controller
{
    /**
     *
     */
    private const DEFAULT_SUPPLIER = 'expedia';
    /**
     *
     */
    private const TYPE_HOTEL = 'hotel';
    /**
     *
     */
    private const TYPE_FLIGHT = 'flight';
    /**
     *
     */
    private const TYPE_COMBO = 'combo';
    /**
     *
     */
    private const ROUTE_SEARCH = 'search';
    /**
     *
     */
    private const ROUTE_DETAIL = 'detail';
    /**
     *
     */
    private const ROUTE_PRICE = 'price';
    /**
     * @var RouteApiStrategy
     */
    private RouteApiStrategy $strategy;
    /**
     * @var ExpediaService
     */
    private ExpediaService $expediaService;

    /**
     * @param RouteApiStrategy $strategy
     * @param ExpediaService $expediaService
     */
    public function __construct(RouteApiStrategy $strategy, ExpediaService $expediaService)
    {
        $this->strategy = $strategy;
        $this->expediaService = $expediaService;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function handle(Request $request): mixed
    {
        $type = $request->get('type');
        $route = Route::currentRouteName();

        if (!self::isTypeValid($type)) return response()->json(['message' => 'Invalid type'], 400);
        if (!self::isRouteValid($route)) return response()->json(['message' => 'Invalid route'], 400);

        // TODO: [UJV-3] Get supplier from DB use config Admin Panel
        $expedia = self::DEFAULT_SUPPLIER;
        $expediaId = Supplier::where('name', $expedia)->first()->id;
        $suppliersIds = [$expediaId];

        $dataHandler = match ($type) {
            'hotel' => new HotelApiHanlder($this->expediaService),
            'flight' => new FlightApiHandler(),
            'combo' => new ComboApiHandler(),
            default => response()->json(['error' => 'Invalid route'], 400),
        };

        return match ($route) {
            'search' => $dataHandler->search($request, $suppliersIds),
            'detail' => $dataHandler->detail($request, $suppliersIds),
            'price' => $dataHandler->price($request, $suppliersIds),
            default => response()->json(['error' => 'Invalid route'], 400),
        };
    }

	public function destinations(Request $request): JsonResponse
    {
		if (empty($request->get('city'))) {
			return response()->json(['error' => 'Invalid city'], 400);
		}
		if (strlen($request->get('city')) < 3) {
			return response()->json(['error' => 'Invalid city, string must be 3 characters or more'], 400);
		}

		$destinations = GiataGeography::
			select(DB::raw('CONCAT(city_name, ", ", country_name, " (", country_code, ", ", locale_name, ")") AS full_name'), 'city_id')
			->where('city_name', 'like', '%'.$request->get('city').'%')
			->limit(35)
			->orderBy('city_name', 'asc')
			->get()
			->pluck('city_id','full_name')
			->toArray();

		$response = [
            'success' => true,
            'data' => $destinations,
        ];
		$res = response()->json($response);

        return $res;

	}

    /**
     * @param $value
     * @return bool
     */
    public static function isTypeValid($value): bool
    {
        return in_array($value, [self::TYPE_HOTEL, self::TYPE_FLIGHT, self::TYPE_COMBO], true);
    }

    /**
     * @param $value
     * @return bool
     */
    public static function isRouteValid($value): bool
    {
        return in_array($value, [self::ROUTE_SEARCH, self::ROUTE_DETAIL, self::ROUTE_PRICE], true);
    }
}
