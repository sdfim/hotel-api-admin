<?php

namespace Modules\AdministrationSuite\Http\Controllers;


use Illuminate\Http\Request;

class SearchInspectorController extends Controller
{
    public function index ()
    {
        return view('dashboard.search-inspector.index');
    }
}
