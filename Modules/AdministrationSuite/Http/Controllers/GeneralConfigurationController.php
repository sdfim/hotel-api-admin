<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use Illuminate\Http\Request;

class GeneralConfigurationController extends Controller
{
    public function index(){
        return view('dashboard.general-configuration');
    }
}
