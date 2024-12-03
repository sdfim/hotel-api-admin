<?php

namespace Modules\Insurance\Http\Controllers;

use Illuminate\View\View;
use Modules\AdministrationSuite\Http\Controllers\Controller;

class InsuranceRestrictionsController extends Controller
{
    public function index(): View
    {
        return view('dashboard.insurance.restrictions.index', ['viewAll' => true]);
    }
}

