<?php

namespace Modules\AdministrationSuite\Http\Controllers;


use Illuminate\Http\Request;

class PropertyMappingController extends Controller
{
    public function index(){
        return view('dashboard.property-mapping');
    }
}
