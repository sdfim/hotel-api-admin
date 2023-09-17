<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use Illuminate\Http\Request;

class PricingRulesController extends Controller
{
    public function index(){
        return view('dashboard.pricing-rules');
    }
}
