<?php

namespace Modules\AdministrationSuite\Http\Controllers;
// namespace App\Http\Controllers;
use Illuminate\Http\Request;

class ReservationsController extends Controller
{
    public function index(){
        return view('dashboard.reservations');
    }
}
