<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use Illuminate\View\View;

class ContentController extends Controller
{
    public function index (): View
    {
        return view('dashboard.content');
    }
}
