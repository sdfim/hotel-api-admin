<?php

namespace Modules\AdministrationSuite\Http\Controllers;


use Illuminate\Http\Request;

class InspectorController extends Controller
{
    public function index(){
        return view('dashboard.inspector');
    }
}
