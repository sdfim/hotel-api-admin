<?php

namespace Modules\AdministrationSuite\Http\Controllers;


use Illuminate\Http\Request;

class GeographyController extends Controller
{
    public function index(){
        return view('dashboard.geography');
    }
}
