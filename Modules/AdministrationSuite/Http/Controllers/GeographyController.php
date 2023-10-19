<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use Illuminate\View\View;

class GeographyController extends Controller
{
    public function index (): View
    {
        return view('dashboard.geography');
    }
}
