<?php

namespace Modules\AdministrationSuite\Http\Controllers;


use Illuminate\Http\Request;

class BookingInspectorController extends Controller
{
    public function index ()
    {
        return view('dashboard.booking-inspector.index');
    }
}
