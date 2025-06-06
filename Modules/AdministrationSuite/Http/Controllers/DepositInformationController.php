<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\DepositInformation;
use Illuminate\Contracts\View\View;

class DepositInformationController extends BaseWithPolicyController
{
    protected static string $model = DepositInformation::class;

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.deposit-information.index');
    }
}
