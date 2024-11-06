<?php

namespace Modules\Insurance\Http\Controllers;

use Illuminate\View\View;
use Modules\AdministrationSuite\Http\Controllers\Controller;

class InsuranceProvidersController extends Controller
{
    public function index(): View
    {
        return view('dashboard.insurance.providers.index');
    }
}
