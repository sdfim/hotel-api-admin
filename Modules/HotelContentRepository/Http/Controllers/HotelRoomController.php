<?php

namespace Modules\HotelContentRepository\Http\Controllers;

use Illuminate\Contracts\View\View;
use Modules\AdministrationSuite\Http\Controllers\BaseWithPolicyController;
use Modules\HotelContentRepository\Models\HotelRoom;

class HotelRoomController extends BaseWithPolicyController
{
    protected static string $model = HotelRoom::class;

    private array $message = ['edit' => 'Edit Hotel Room'];

    public function index(): View
    {
        return view('dashboard.hotel_repository.hotel_rooms.index');
    }
}
