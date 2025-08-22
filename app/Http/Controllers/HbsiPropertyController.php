<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HbsiPropertyController extends Controller
{
    public function index()
    {
        return view('dashboard.hbsi-property.index');
    }
}

