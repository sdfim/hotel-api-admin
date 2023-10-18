<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApiExceptionReport;
use Illuminate\View\View;

class ContentLoaderExceptionsController extends Controller
{
    private array $message = ['show' => 'Show Response'];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('dashboard.content-loader-exceptions.index');
    }
   
     /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $text = $this->message;
        $report = ApiExceptionReport::findOrFail($id);
        return view('dashboard.content-loader-exceptions.show', compact('report', 'text'));
    }

}
