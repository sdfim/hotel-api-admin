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
        $reservations = Reservations::with(['channel','contains'])->get();
        return view('dashboard.reservations.index',[
            'reservations' => $reservations
        ]);
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
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return redirect()->route('reservations.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        return redirect()->route('reservations.index');
    }
    
}