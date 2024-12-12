<?php

namespace Modules\HotelContentRepository\Http\Controllers;

use Modules\AdministrationSuite\Http\Controllers\BaseWithPolicyController;
use Modules\HotelContentRepository\Models\HotelRoom;
use Illuminate\Contracts\View\View;

class HotelRoomController extends BaseWithPolicyController
{
    protected static string $model = HotelRoom::class;

    private array $message = ['edit' => 'Edit Hotel Room'];

    public function index(): View
    {
        return view('dashboard.hotel_repository.hotel_rooms.index');
    }

    public function show(string $id): View
    {
        $hotelRoom = HotelRoom::findOrFail($id);
        return view('dashboard.hotel_repository..hotel_rooms.show', compact('hotelRoom'));
    }

    public function edit(string $id): View
    {
        $hotelRoom = HotelRoom::findOrFail($id);
        $text = $this->message;
        $hotelId = $hotelRoom->hotel_id;

        return view('dashboard.hotel_repository.hotel_rooms.edit', compact('hotelRoom', 'text', 'hotelId'));
    }

    public function create(): View
    {
        $text = $this->message;
        return view('dashboard.hotel_repository..hotel_rooms.create', compact('text'));
    }
}
