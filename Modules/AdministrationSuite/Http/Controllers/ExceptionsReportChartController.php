<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use Illuminate\View\View;

class ExceptionsReportChartController extends Controller
{

    /**
     * Display a listing of the resource.
     * @return View
     */
    public function index(): View
    {
        return view('dashboard.exceptions-report.chart');
    }
}
