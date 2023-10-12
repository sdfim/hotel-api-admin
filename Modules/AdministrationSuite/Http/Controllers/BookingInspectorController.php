<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\ApiBookingInspector;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingInspectorController extends Controller
{
    private $message = ['show' => 'Show Response'];

    /**
     * Display a listing of the resource.
     */
    public function index ():View
    {
        return view('dashboard.booking-inspector.index');
    }

   
     /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $text = $this->message;
        $inspector = ApiBookingInspector::findOrFail($id);
        return view('dashboard.booking-inspector.show', compact('inspector', 'text'));
    }

    
}
