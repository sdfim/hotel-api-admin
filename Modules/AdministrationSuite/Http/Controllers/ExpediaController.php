<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\ExpediaContent;
use Illuminate\Contracts\View\View;

class ExpediaController extends BaseWithPolicyController
{
    protected static string $model = ExpediaContent::class;

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.expedia.index');
    }
}
