<?php

namespace App\Http\Controllers;

use App\Models\FailedJob;
use Illuminate\Http\Request;

class FailedJobController extends Controller
{
    /**
     * Display a listing of the failed jobs.
     */
    public function index()
    {
        return view('dashboard.failed_jobs.index');
    }
}
