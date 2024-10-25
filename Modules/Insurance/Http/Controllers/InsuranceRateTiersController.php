<?php

namespace Modules\Insurance\Http\Controllers;

use Illuminate\View\View;
use Modules\AdministrationSuite\Http\Controllers\Controller;

class InsuranceRateTiersController extends Controller
{
    public function index(): View
    {
        return view('dashboard.insurance.rate-tiers.index');
    }
}
