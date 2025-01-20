<?php

namespace Modules\HotelContentRepository\Http\Controllers;

use Modules\AdministrationSuite\Http\Controllers\BaseWithPolicyController;
use Modules\HotelContentRepository\Models\Hotel;
use Illuminate\Contracts\View\View;

class HotelController extends BaseWithPolicyController
{
    protected static string $model = Hotel::class;
    protected static ?string $parameterName = 'hotel_repository';

    private array $message = ['edit' => 'Edit', 'create' => 'Create new Hotel'];


    public function index(): View
    {
        return view('dashboard.hotel_repository.index');
    }

    public function show(string $id): View
    {
        $hotel = Hotel::findOrFail($id);

        return view('dashboard.hotel_repository.form', compact('hotel'));
    }

    public function edit(string $id): View
    {
        $hotel = Hotel::findOrFail($id);
        $text = $this->message;
        $hotelId = $hotel->id;
        $productId = $hotel->product->id;

        return view('dashboard.hotel_repository.form', compact('hotel', 'text', 'hotelId', 'productId'));
    }

    public function create(): View
    {
        $text = $this->message;
        $hotel = new Hotel();
        $hotelId = 0;
        $productId = 0;

        return view('dashboard.hotel_repository.form', compact('hotel','text', 'hotelId', 'productId'));
    }
}
