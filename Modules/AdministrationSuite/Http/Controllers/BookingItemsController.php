<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\ApiBookingItem;
use Illuminate\View\View;

class BookingItemsController extends Controller
{
    private array $message = ['show' => 'Show Response'];

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.booking-items.index');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $text = $this->message;
        $item = ApiBookingItem::findOrFail($id);
        return view('dashboard.booking-items.show', compact('item', 'text'));
    }


}
