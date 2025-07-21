<?php

namespace Modules\HotelContentRepository\Http\Controllers;

use Illuminate\Contracts\View\View;
use Modules\AdministrationSuite\Http\Controllers\BaseWithPolicyController;
use Modules\HotelContentRepository\Models\Hotel;

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
        $product = $hotel->product;

        return view('dashboard.hotel_repository.form', compact('hotel', 'text', 'hotel', 'product'));
    }

    public function create(): View
    {
        $text = $this->message;
        $hotel = new Hotel;
        $product = 0;

        return view('dashboard.hotel_repository.form', compact('hotel', 'text', 'hotel', 'product'));
    }
}
