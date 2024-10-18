<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use Illuminate\View\View;

class PermissionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __invoke(): View
    {
        return view('dashboard.permissions.index');
    }
}
