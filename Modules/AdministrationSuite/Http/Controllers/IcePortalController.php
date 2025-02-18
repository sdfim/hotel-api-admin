<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\IcePortalProperty;
use Illuminate\Contracts\View\View;

class IcePortalController extends BaseWithPolicyController
{
    protected static string $model = IcePortalProperty::class;

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.ice-portal.index');
    }
}
