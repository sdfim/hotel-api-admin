<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\Reservations;
use Illuminate\View\View;

class ReservationsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index():View
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
}
