<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\IcePortalPropertyAsset;
use Illuminate\Contracts\View\View;

class IcePortalController extends BaseWithPolicyController
{
    protected static string $model = IcePortalPropertyAsset::class;

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.ice-portal.index');
    }
}
