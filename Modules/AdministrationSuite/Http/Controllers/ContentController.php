<?php

namespace Modules\AdministrationSuite\Http\Controllers;


use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function index ()
    {
        return view('dashboard.content');
    }
}
