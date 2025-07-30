<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\HotelTraderContentHotel;
use Illuminate\Contracts\View\View;

class HotelTraderController extends BaseWithPolicyController
{
    protected static string $model = HotelTraderContentHotel::class;

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.hotel-trader.index');
    }
}

