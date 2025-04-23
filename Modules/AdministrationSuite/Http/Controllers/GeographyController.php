<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\GiataGeography;
use Illuminate\View\View;

class GeographyController extends BaseWithPolicyController
{
    protected static string $model = GiataGeography::class;

    public function index(): View
    {
        return view('dashboard.geography');
    }
}
