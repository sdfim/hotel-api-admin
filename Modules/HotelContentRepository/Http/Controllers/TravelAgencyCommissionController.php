<?php

namespace Modules\HotelContentRepository\Http\Controllers;

use Illuminate\Contracts\View\View;
use Modules\AdministrationSuite\Http\Controllers\BaseWithPolicyController;
use Modules\HotelContentRepository\Models\TravelAgencyCommission;

class TravelAgencyCommissionController extends BaseWithPolicyController
{
    protected static string $model = TravelAgencyCommission::class;

    private array $message = ['edit' => 'Edit Travel Agency Commission'];

    public function index(): View
    {
        return view('dashboard.commissions.index');
    }

    public function show(string $id): View
    {
        $commission = TravelAgencyCommission::findOrFail($id);

        return view('dashboard.commissions.show', compact('commission'));
    }

    public function edit(string $id): View
    {
        $commission = TravelAgencyCommission::findOrFail($id);
        $text = $this->message;
        $commissionId = $commission->id;

        return view('dashboard.commissions.edit', compact('commission', 'text', 'commissionId'));
    }

    public function create(): View
    {
        $text = $this->message;

        return view('dashboard.commissions.create', compact('text'));
    }
}
