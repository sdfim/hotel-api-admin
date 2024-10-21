<?php

namespace Modules\HotelContentRepository\Http\Controllers;

use Modules\HotelContentRepository\Models\Hotel;
use Illuminate\Contracts\View\View;
use Modules\AdministrationSuite\Http\Controllers\Controller;

class HotelController extends Controller
{
    private array $message = ['edit' => 'Edit Hotel'];


    public function index(): View
    {
        return view('dashboard.hotel_repository.index');
    }

    public function show(string $id): View
    {
        $hotelRoom = Hotel::findOrFail($id);

        return view('dashboard.hotel_repository.show', compact('hotelRoom'));
    }

    public function edit(string $id): View
    {
        $hotel = Hotel::findOrFail($id);
        $text = $this->message;
        $hotelId = $hotel->id;

        return view('dashboard.hotel_repository.edit', compact('hotel', 'text', 'hotelId'));
    }

    public function create(): View
    {
        $text = $this->message;

        return view('dashboard.hotel_repository.create', compact('text'));
    }
}
