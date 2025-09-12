<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class AirwallexApiLogController extends Controller
{
    /**
     * Display the AirwallexApiLog table view.
     */
    public function index(): View
    {
        return view('dashboard.airwallex_api_log.index');
    }
}

