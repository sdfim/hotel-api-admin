<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\ApiBookingInspector;
use Illuminate\View\View;

class BookingInspectorController extends Controller
{
    private array $message = ['show' => 'Show Response'];

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.booking-inspector.index');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $text = $this->message;
        $inspector = ApiBookingInspector::findOrFail($id);
        return view('dashboard.booking-inspector.show', compact('inspector', 'text'));
    }


}
