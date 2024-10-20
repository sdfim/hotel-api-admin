<?php

namespace Modules\HotelContentRepository\Http\Controllers;

use Modules\AdministrationSuite\Http\Controllers\BaseWithPolicyController;
use Modules\HotelContentRepository\Models\Hotel;
use Illuminate\Contracts\View\View;

class HotelController extends BaseWithPolicyController
{
    protected static string $model = Hotel::class;

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
