<?php

namespace Modules\HotelContentRepository\Http\Controllers;

use Modules\HotelContentRepository\Models\Hotel;
use Illuminate\Contracts\View\View;
use Modules\AdministrationSuite\Http\Controllers\Controller;

class HotelController extends Controller
{
    public function index(): View
    {
        return view('dashboard.hotel_repository.index');
    }

    public function show(string $id): View
    {
        $hotelRoom = Hotel::findOrFail($id);

        return view('dashboard.hotel_repository.show', compact('hotelRoom'));
    }
}
