<?php

namespace Modules\HotelContentRepository\Http\Controllers;

use Illuminate\Contracts\View\View;
use Modules\AdministrationSuite\Http\Controllers\Controller;
use Modules\HotelContentRepository\Models\Product;

class PdGridController extends Controller
{
    protected static string $model = Product::class;

    protected static ?string $parameterName = 'pd-grid';

    public function index(): View
    {
        return view('dashboard.hotel_repository.pd-grid.index');
    }
}
