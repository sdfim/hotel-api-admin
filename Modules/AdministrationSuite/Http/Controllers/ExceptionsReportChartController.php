<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\ApiExceptionReport;
use Illuminate\View\View;

class ExceptionsReportChartController extends BaseWithPolicyController
{
    protected static string $model = ApiExceptionReport::class;

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.exceptions-report.chart');
    }
}
