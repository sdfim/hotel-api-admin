<?php

namespace Modules\AdministrationSuite\Http\Controllers\Configurations;

use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;
use Modules\AdministrationSuite\Http\Controllers\Controller;

class GroupConfigController extends Controller
{
    public function index(): View
    {
        Gate::authorize('config-group');

        return view('dashboard.configurations.config-group.index');
    }
}
