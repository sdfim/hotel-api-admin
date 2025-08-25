<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class HiltonPropertyController extends Controller
{
    public function index()
    {
        return view('dashboard.hilton.index');
    }
}

