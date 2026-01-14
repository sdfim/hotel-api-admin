<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\Reservation;
use App\Repositories\ApiBookingInspectorRepository;
use Illuminate\View\View;

class ReservationsController extends BaseWithPolicyController
{
    protected static string $model = Reservation::class;

    private array $message = [
        'create' => 'Add New Reservations',
        'edit' => 'Edit Reservations',
        'show' => 'Show Reservations',
    ];

    /**
     * Display a list of reservations
     */
    public function index(): View
    {
        $reservations = Reservation::with(['channel'])->get();

        return view('dashboard.reservations.index', [
            'reservations' => $reservations,
        ]);
    }

    /**
     * Display a single reservation with advisor email
     */
    public function show(string $id): View
    {
        $text = $this->message;

        // Load reservation with soft-deleted channel relation and booking item for commission
        $reservation = Reservation::with([
            'channel' => function ($q) {
                $q->withTrashed();
            },
            'apiBookingsMetadata',
            'apiBookingItem.supplier',
            'apiBookingItem.search',
        ])->findOrFail($id);

        // Decode JSON payload from reservation_contains
        $contains = json_decode($reservation->reservation_contains, true) ?? [];

        // Get booking identifiers from the payload (fallback to columns)
        $bookingItem = $contains['booking_item'] ?? $reservation->booking_item;

        // Retrieve advisor email from add_item (complete) request
        [, , $advisorEmail] = ApiBookingInspectorRepository::getEmailAgentBookingItem($bookingItem)
            ?? [null, null, null];

        // Calculate advisor commission
        $advisorCommission = 0;
        if ($reservation->apiBookingItem) {
            $totalPrice = (float) \Illuminate\Support\Arr::get($contains, 'price.total_price', $reservation->total_cost ?? 0);
            $advisorCommission = app(\App\Services\AdvisorCommissionService::class)->calculate($reservation->apiBookingItem, $totalPrice);
        }

        return view('dashboard.reservations.show', compact('reservation', 'text', 'advisorEmail', 'advisorCommission'));
    }
}
