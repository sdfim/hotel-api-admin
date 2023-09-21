<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservations;
use Illuminate\View\View;

class ReservationsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reservations = Reservations::with(['channel','contains'])->whereNull('canceled_at')->get();
        return view('dashboard.reservations.index',[
            'reservations' => $reservations
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
	 * Display the specified resource.
	 */
	public function show(String $id): View
	{
		$reservation = Reservations::with(['channel','contains'])->findOrFail($id);

		return view('dashboard.reservations.show', compact('reservation'));
	}

    /**
     * Canceled reservation.
     */
    public function cancel(string $id)
    {
        $reservations = Reservations::findOrFail($id);
        $reservations->update(['canceled_at' => date('Y-m-d H:i:s')]);
        return back();
    }

}
