<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use Illuminate\View\View;
use Carbon\Carbon;
use App\Models\ApiExceptionReport;

class ContentLoaderExceptionsChartsController extends Controller
{

    /**
     * Display a listing of the resource.
     * @return View
     */
    public function index(): View
    {
        $currentDate = Carbon::now();
        $dateArray = [];
        $successCount = [];
        $errorsCount = [];

        $daysCount = 30; // How many days to display in the chart

        for ($i = 0; $i < $daysCount; $i++) {
            $dateArray[] = $currentDate->subDay()->toDateString();
        }
        
        for($i = 0; $i < count($dateArray); $i++){
            $successCount[$i] = ApiExceptionReport::whereDate('created_at', $dateArray[$i])->where('level','success')->count();
            $errorsCount[$i] = ApiExceptionReport::whereDate('created_at', $dateArray[$i])->where('level','error')->count();
        }

        return view('dashboard.content-loader-exceptions.charts', ['dates' => $dateArray, 'errors' => $errorsCount, 'success' => $successCount]);
    }
}
