<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class StatisticChartsController extends Controller
{
    public function index(): View
    {
        Gate::authorize('statistic-charts');

        return view('dashboard.statistic-charts.index');
    }
}
