<?php

namespace Modules\HotelContentRepository\Http\Controllers;

use Illuminate\Contracts\View\View;
use Modules\AdministrationSuite\Http\Controllers\BaseWithPolicyController;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelRate;

class HotelRateController extends BaseWithPolicyController
{
    protected static string $model = HotelRate::class;

    private array $message = ['edit' => 'Edit Hotel Rate', 'create' => 'Create Hotel Rate'];

    public function index(): View
    {
        return view('dashboard.hotel_repository.hotel_rates.index');
    }

    public function create(): View
    {
        $hotelId = request()->query('hotelId');
        $hotel = $hotelId ? Hotel::findOrFail($hotelId) : null;
        $text = $this->message;
        $hotelRate = new HotelRate;
        $product = null;

        return view('dashboard.hotel_repository.hotel_rates.form', compact('hotelRate', 'text', 'hotel', 'product'));
    }

    public function edit(string $id): View
    {
        $hotelRate = HotelRate::findOrFail($id);
        $hotel = $hotelRate->hotel;
        $product = $hotelRate->hotel->product;
        $text = $this->message;

        return view('dashboard.hotel_repository.hotel_rates.form', compact('hotelRate', 'text', 'hotel', 'product'));
    }

    public function destroy(HotelRate $rate): View
    {
        $rate->delete();

        return view('dashboard.hotel_repository.hotel_rates.index');
    }
}
