<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use App\Models\ExpediaContent;

class ExpediaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.expedia.index');
    }
}
