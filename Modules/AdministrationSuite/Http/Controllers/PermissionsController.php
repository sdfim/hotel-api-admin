<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\Permission;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PermissionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __invoke(): View
    {
        Gate::authorize('view', Permission::class);

        return view('dashboard.permissions.index');
    }
}
