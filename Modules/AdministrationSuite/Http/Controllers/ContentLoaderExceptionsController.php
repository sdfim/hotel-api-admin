<?php

namespace Modules\AdministrationSuite\Http\Controllers;


use Illuminate\Http\Request;

class ContentLoaderExceptionsController extends Controller
{
    public function index ()
    {
        return view('dashboard.content-loader-exceptions');
    }
}
