<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use Illuminate\View\View;

class ContentLoaderExceptionsController extends Controller
{
    public function index (): View
    {
        return view('dashboard.content-loader-exceptions');
    }
}
