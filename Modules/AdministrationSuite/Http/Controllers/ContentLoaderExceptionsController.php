<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use Illuminate\View\View;

class ContentLoaderExceptionsController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index():View
    {
        return view('dashboard.content-loader-exceptions.index');
    }
}
