<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class OracleContentController extends Controller
{
    public function index()
    {
        return view('dashboard.oracle.index');
    }
}

