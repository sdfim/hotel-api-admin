<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use Illuminate\Http\Request;

class ChanelsConfigurationController extends Controller
{
    public function index(){
        return view('dashboard.chanels-configuration');
    }
}
