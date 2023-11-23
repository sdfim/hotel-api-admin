<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\Reservation;
use Illuminate\View\View;

class ReservationsController extends Controller
{
    /**
     * @var array|string[]
     */
    private array $message = ['create' => 'Add New Reservations', 'edit' => 'Edit Reservations', 'show' => 'Show Reservations'];

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $reservations = Reservation::with(['channel'])->get();
        return view('dashboard.reservations.index', [
            'reservations' => $reservations
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $text = $this->message;
        $reservation = Reservation::with(['channel'])->findOrFail($id);
        return view('dashboard.reservations.show', compact('reservation', 'text'));
    }
}
