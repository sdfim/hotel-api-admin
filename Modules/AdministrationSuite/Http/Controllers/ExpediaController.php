<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use Illuminate\Contracts\View\View;

class ExpediaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.expedia.index');
    }

	public function charts(): View
    {
        return view('dashboard.expedia.charts');
    }
}
