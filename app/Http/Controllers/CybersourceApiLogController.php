<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class CybersourceApiLogController extends Controller
{
    /**
     * Display the CybersourceApiLog table view.
     */
    public function index(): View
    {
        return view('dashboard.cybersource_api_log.index');
    }
}
