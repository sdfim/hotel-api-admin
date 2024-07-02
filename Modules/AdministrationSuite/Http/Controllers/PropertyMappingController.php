<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use Illuminate\View\View;

class PropertyMappingController extends Controller
{
    public function index(): View
    {
        return view('dashboard.property-mapping');
    }
}
