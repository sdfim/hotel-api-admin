<?php

namespace Modules\HotelContentRepository\Http\Controllers;

use Modules\HotelContentRepository\Models\HotelImage;
use Illuminate\View\View;
use Modules\AdministrationSuite\Http\Controllers\BaseWithPolicyController;

class HotelImageController extends BaseWithPolicyController
{
    protected static string $model = HotelImage::class;

    private array $message = ['edit' => 'Edit Hotel Image', 'create' => 'Create Hotel Image'];

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.hotel-images.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $hotelImage = HotelImage::findOrFail($id);
        $text = $this->message;

        return view('dashboard.hotel-images.form', compact('hotelImage', 'text'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $hotelImage = new HotelImage();
        $text = $this->message;

        return view('dashboard.hotel-images.form', compact('hotelImage', 'text'));
    }
}
