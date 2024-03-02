<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use Illuminate\Contracts\View\View;

class StatisticChartsController extends Controller
{
    public function index(): View
    {
        return view('dashboard.statistic-charts.index');
    }
}
